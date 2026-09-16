<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendTelegramSessionQR;
use App\Models\AttendanceSession;
use App\Models\AttendanceToken;
use App\Models\Course;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceSessionController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
    ) {}

    /**
     * Start a new attendance session with geofence and generate first rotating token.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTeacher()) {
            return response()->json(['message' => 'Only teachers can start attendance sessions.'], 403);
        }

        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'verification_mode' => ['nullable', 'string', 'in:location,wifi,both'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['nullable', 'integer', 'min:10', 'max:1000'],
            'require_wifi' => ['nullable', 'boolean'],
            'wifi_subnet' => ['nullable', 'string', 'max:255'],
        ]);

        $mode = $validated['verification_mode'] ?? 'location';
        if ($mode === 'location' && (!isset($validated['latitude']) || !isset($validated['longitude']))) {
            return response()->json([
                'message' => 'The latitude and longitude fields are required when location verification is selected.',
                'errors' => [
                    'latitude' => ['Classroom latitude is required for location verification.'],
                    'longitude' => ['Classroom longitude is required for location verification.'],
                ],
            ], 422);
        }

        $course = Course::findOrFail($validated['course_id']);
        if ($course->teacher_id !== $user->id) {
            return response()->json(['message' => 'You do not teach this course.'], 403);
        }

        // Close any previously opened sessions for this course
        AttendanceSession::where('course_id', $course->id)
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'ends_at' => now(),
            ]);

        $requireWifi = ($mode === 'wifi' || $mode === 'both');

        $session = AttendanceSession::create([
            'course_id' => $course->id,
            'teacher_id' => $user->id,
            'started_at' => now(),
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'radius_meters' => $validated['radius_meters'] ?? 100,
            'verification_mode' => $mode,
            'require_wifi' => $requireWifi,
            'wifi_subnet' => $requireWifi ? ($validated['wifi_subnet'] ?? null) : null,
            'status' => 'open',
        ]);

        $token = $this->attendanceService->generateToken($session);

        // Dispatch after response — sends QR image immediately after response is sent to teacher,
        // without blocking teacher UI or requiring a separate queue worker process.
        SendTelegramSessionQR::dispatchAfterResponse($session->id, $token->token);

        return response()->json([
            'message' => 'Attendance session started.',
            'session' => $session->load('course:id,name'),
            'token' => $token->token,
            'expires_at' => $token->expires_at->toIso8601String(),
            'validity_seconds' => AttendanceService::DEFAULT_VALIDITY_SECONDS,
        ], 201);
    }

    /**
     * Get or refresh current rotating token for session display screen.
     */
    public function currentToken(Request $request, AttendanceSession $session): JsonResponse
    {
        $user = $request->user();

        if ($session->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$session->isOpen()) {
            return response()->json([
                'message' => 'Attendance session is closed.',
                'status' => 'closed',
            ], 410);
        }

        // Find existing valid unredeemed token
        $token = AttendanceToken::where('session_id', $session->id)
            ->whereNull('redeemed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        // If no active valid token, generate a fresh rotating token
        if (!$token) {
            $token = $this->attendanceService->generateToken($session);
        }

        $secondsRemaining = max(0, now()->diffInSeconds($token->expires_at, false));

        return response()->json([
            'token' => $token->token,
            'expires_at' => $token->expires_at->toIso8601String(),
            'seconds_remaining' => (int) $secondsRemaining,
            'status' => $session->status,
        ]);
    }

    /**
     * Close the attendance session.
     */
    public function close(Request $request, AttendanceSession $session): JsonResponse
    {
        $user = $request->user();

        if ($session->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $session->close();

        // Expire all pending tokens for this session
        AttendanceToken::where('session_id', $session->id)
            ->whereNull('redeemed_at')
            ->update(['expires_at' => now()]);

        return response()->json([
            'message' => 'Attendance session closed successfully.',
            'session' => $session,
        ]);
    }

    /**
     * Session attendance report (live check-in feed and per-session summary).
     */
    public function report(Request $request, AttendanceSession $session): JsonResponse
    {
        $session->load([
            'course.students:id,name,email',
            'records.student:id,name,email',
        ]);

        $enrolledStudents = $session->course->students;
        $recordsByStudent = $session->records->keyBy('student_id');

        $roster = $enrolledStudents->map(function ($student) use ($recordsByStudent) {
            $record = $recordsByStudent->get($student->id);

            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'status' => $record ? $record->status : 'absent',
                'checked_in_at' => $record ? $record->checked_in_at?->toIso8601String() : null,
                'method' => $record ? $record->method : null,
                'latitude' => $record ? $record->latitude : null,
                'longitude' => $record ? $record->longitude : null,
                'record_id' => $record ? $record->id : null,
            ];
        });

        $counts = [
            'total_enrolled' => $enrolledStudents->count(),
            'present' => $roster->where('status', 'present')->count(),
            'late' => $roster->where('status', 'late')->count(),
            'excused' => $roster->where('status', 'excused')->count(),
            'absent' => $roster->where('status', 'absent')->count(),
        ];

        return response()->json([
            'session' => $session,
            'counts' => $counts,
            'roster' => $roster,
        ]);
    }

    /**
     * Ping / verification endpoint for school WiFi presence.
     */
    public function wifiVerify(Request $request): JsonResponse
    {
        $clientIp = $request->input('simulate_ip') ?: $request->ip();
        $sessionId = $request->input('session_id');
        $session = $sessionId ? AttendanceSession::find($sessionId) : null;

        $verification = $this->attendanceService->verifyWifi($clientIp, $session);

        return response()->json($verification);
    }
}
