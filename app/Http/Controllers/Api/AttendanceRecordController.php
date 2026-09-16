<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceRecordController extends Controller
{
    /**
     * Teacher manual override of an existing attendance record.
     */
    public function update(Request $request, AttendanceRecord $record): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTeacher()) {
            return response()->json(['message' => 'Only teachers can override attendance records.'], 403);
        }

        $session = $record->session;
        if ($session->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized for this session.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:present,late,absent,excused'],
        ]);

        $record->update([
            'status' => $validated['status'],
            'method' => 'manual',
        ]);

        return response()->json([
            'message' => 'Attendance record updated successfully.',
            'record' => $record->load('student:id,name,email'),
        ]);
    }

    /**
     * Teacher manual check-in / override for a student in a session.
     * Useful when GPS or camera fails for a legitimate student in the room.
     */
    public function manualRecord(Request $request, AttendanceSession $session): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTeacher() || $session->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:present,late,absent,excused'],
        ]);

        $record = AttendanceRecord::updateOrCreate(
            [
                'session_id' => $session->id,
                'student_id' => $validated['student_id'],
            ],
            [
                'status' => $validated['status'],
                'checked_in_at' => now(),
                'method' => 'manual',
            ]
        );

        return response()->json([
            'message' => 'Manual attendance record saved.',
            'record' => $record->load('student:id,name,email'),
        ]);
    }

    /**
     * Per-student attendance history and statistics.
     */
    public function studentHistory(Request $request, User $student): JsonResponse
    {
        $currentUser = $request->user();

        // A student can only view their own history unless the requester is a teacher
        if ($currentUser->isStudent() && $currentUser->id !== $student->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $records = AttendanceRecord::where('student_id', $student->id)
            ->with(['session.course'])
            ->latest('checked_in_at')
            ->get();

        $enrolledCourses = $student->enrolledCourses()->withCount('sessions')->get();
        $totalSessionsInEnrolledCourses = $enrolledCourses->sum('sessions_count');

        $presentCount = $records->where('status', 'present')->count();
        $lateCount = $records->where('status', 'late')->count();
        $excusedCount = $records->where('status', 'excused')->count();
        $absentCount = max(0, $totalSessionsInEnrolledCourses - ($presentCount + $lateCount + $excusedCount));

        $totalAttended = $presentCount + $lateCount;
        $attendanceRate = $totalSessionsInEnrolledCourses > 0
            ? round(($totalAttended / $totalSessionsInEnrolledCourses) * 100, 1)
            : 0;

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
            ],
            'stats' => [
                'total_sessions' => $totalSessionsInEnrolledCourses,
                'present' => $presentCount,
                'late' => $lateCount,
                'excused' => $excusedCount,
                'absent' => $absentCount,
                'attendance_rate' => $attendanceRate,
            ],
            'records' => $records,
            'enrolled_courses' => $enrolledCourses,
        ]);
    }
}
