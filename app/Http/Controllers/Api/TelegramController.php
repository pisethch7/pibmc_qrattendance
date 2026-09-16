<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService,
    ) {}

    /**
     * Get the current status, bot info, and diagnostics of Telegram integration.
     */
    public function status(): JsonResponse
    {
        $status = $this->telegramService->getStatus();

        return response()->json([
            'status' => 'ok',
            'data'   => $status,
        ]);
    }

    /**
     * Send a test message to the configured Telegram chat.
     */
    public function test(Request $request): JsonResponse
    {
        $user = $request->user();
        $senderName = $user ? $user->name : 'Administrator';

        $text = "🔔 *Telegram Integration Test*\n\n"
              . "✅ *Status:* Online & Verified\n"
              . "👤 *Triggered by:* {$senderName}\n"
              . "🏫 *App:* " . config('app.name', 'PIBMC Attendance') . "\n"
              . "🌐 *Server:* " . config('app.url') . "\n"
              . "⏰ *Time:* " . now()->setTimezone('Asia/Phnom_Penh')->format('d M Y, H:i:s') . "\n\n"
              . "🎉 Both live QR code sending and student check-in alerts are active!";

        $result = $this->telegramService->sendTestMessage($text);

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}
