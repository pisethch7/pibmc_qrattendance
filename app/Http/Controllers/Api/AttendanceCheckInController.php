<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceCheckInController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected TelegramService $telegramService,
    ) {}

    /**
     * Process student scan check-in.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'Only enrolled students can check in.',
            ], 403);
        }

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'simulate_ip' => ['nullable', 'string'],
        ]);

        $clientIp = (!empty($validated['simulate_ip']) && app()->environment(['local', 'testing']))
            ? $validated['simulate_ip']
            : $request->ip();

        $lat = isset($validated['latitude']) && $validated['latitude'] !== '' && $validated['latitude'] !== null
            ? (float) $validated['latitude']
            : null;

        $lon = isset($validated['longitude']) && $validated['longitude'] !== '' && $validated['longitude'] !== null
            ? (float) $validated['longitude']
            : null;

        $record = $this->attendanceService->checkIn(
            student: $user,
            tokenString: $validated['token'],
            latitude: $lat,
            longitude: $lon,
            clientIp: $clientIp
        );

        // Notify Telegram bot with student check-in details (non-blocking; errors are logged internally)
        $this->telegramService->sendCheckInNotification($record);

        return response()->json([
            'success' => true,
            'message' => "Attendance recorded as {$record->status}!",
            'record' => $record->load('session.course'),
        ]);
    }
}
