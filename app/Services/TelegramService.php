<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $chatId;
    protected string $apiUrl;
    protected bool $enabled;

    public function __construct()
    {
        $this->botToken = (string) config('telegram.bot_token', '');
        $this->chatId   = (string) config('telegram.chat_id', '');
        $this->apiUrl   = (string) config('telegram.api_url', 'https://api.telegram.org/bot');
        $this->enabled  = (bool) config('telegram.enabled', true);
    }

    /**
     * Returns true only when credentials are set and the feature is enabled.
     */
    public function isConfigured(): bool
    {
        return $this->enabled
            && !empty($this->botToken)
            && $this->botToken !== 'your_bot_token_here'
            && !empty($this->chatId)
            && $this->chatId !== 'your_chat_id_here';
    }

    /**
     * Generate a QR-code PNG from a token string and return raw bytes.
     * Uses chillerlan/php-qrcode v6 with GD PNG output.
     */
    protected function generateQrPng(string $tokenString): string
    {
        if (extension_loaded('gd')) {
            $options = new QROptions([
                'outputInterface' => QRGdImagePNG::class,
                'eccLevel'        => EccLevel::H,
                'scale'           => 12,
                'outputBase64'    => false,
                'addQuietzone'    => true,
                'quietzoneSize'   => 4,
            ]);

            return (string) (new QRCode($options))->render($tokenString);
        }

        // Robust fallback if PHP GD extension is missing on server/cloud
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=' . urlencode($tokenString);
        $response = Http::timeout(10)->get($url);
        if ($response->successful()) {
            return $response->body();
        }

        throw new \RuntimeException('Unable to generate QR code: ext-gd is not loaded and fallback service failed.');
    }

    /**
     * Escape special characters for Telegram MarkdownV2.
     */
    protected function escape(string $text): string
    {
        return preg_replace('/([_*\[\]()~`>#+\-=|{}.!\\\\])/', '\\\\$1', $text) ?? $text;
    }

    /**
     * Get detailed configuration status and diagnostics.
     */
    public function getStatus(): array
    {
        $botSet  = !empty($this->botToken) && $this->botToken !== 'your_bot_token_here';
        $chatSet = !empty($this->chatId) && $this->chatId !== 'your_chat_id_here';

        $preview = '';
        if ($botSet) {
            $len = strlen($this->botToken);
            $preview = substr($this->botToken, 0, 4) . '...' . substr($this->botToken, -4);
        }

        $botInfo = null;
        $apiReachable = false;
        $error = null;

        if ($botSet) {
            try {
                $response = Http::timeout(6)->connectTimeout(4)->get("{$this->apiUrl}{$this->botToken}/getMe");
                if ($response->successful() && ($response->json('ok') === true)) {
                    $apiReachable = true;
                    $botInfo = $response->json('result');
                } else {
                    $error = $response->json('description') ?? 'Telegram getMe failed with HTTP ' . $response->status();
                }
            } catch (\Throwable $e) {
                $error = 'Could not reach Telegram API: ' . $e->getMessage();
            }
        }

        return [
            'enabled'             => $this->enabled,
            'configured'          => $this->isConfigured(),
            'bot_token_set'       => $botSet,
            'bot_token_preview'   => $preview,
            'chat_id_set'         => $chatSet,
            'chat_id'             => $this->chatId,
            'gd_installed'        => extension_loaded('gd'),
            'api_reachable'       => $apiReachable,
            'bot_info'            => $botInfo,
            'error'               => $error,
        ];
    }

    /**
     * Send a test text message to verify Telegram bot setup.
     */
    public function sendTestMessage(?string $customText = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Telegram is not configured. Please set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID in your environment variables.',
            ];
        }

        try {
            $serverName = config('app.name', 'PIBMC Attendance');
            $serverUrl  = config('app.url', 'localhost');
            $time       = now()->setTimezone('Asia/Phnom_Penh')->format('d M Y, H:i:s');

            $text = $customText ?? "🔔 *Telegram Bot Connection Test*\n\n✅ *System:* {$serverName}\n🌐 *Server URL:* {$serverUrl}\n⏰ *Time:* {$time}\n\nYour Telegram integration is working properly!";

            $response = Http::timeout(10)->connectTimeout(5)->post("{$this->apiUrl}{$this->botToken}/sendMessage", [
                'chat_id'    => $this->chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                $body = $response->json();
                $desc = $body['description'] ?? $response->body();
                Log::warning("TelegramService: sendTestMessage failed — {$desc}");
                return [
                    'success' => false,
                    'message' => "Telegram API error: {$desc}",
                ];
            }

            Log::info("TelegramService: sendTestMessage succeeded.");
            return [
                'success' => true,
                'message' => 'Test message sent successfully to Telegram!',
                'result'  => $response->json('result'),
            ];

        } catch (\Throwable $e) {
            Log::error("TelegramService: sendTestMessage exception — {$e->getMessage()}");
            return [
                'success' => false,
                'message' => 'Exception while sending to Telegram: ' . $e->getMessage(),
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Send a QR-code photo to the configured Telegram chat when a session starts.
     *
     * Called immediately after AttendanceSessionController::store() creates the session.
     */
    public function sendSessionQR(AttendanceSession $session, string $token): void
    {
        if (!$this->isConfigured()) {
            Log::warning('TelegramService: sendSessionQR skipped — Telegram credentials not set. Set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID.');
            return;
        }

        try {
            $session->loadMissing(['course:id,name', 'teacher:id,name']);

            $courseName  = $this->escape($session->course->name  ?? 'Unknown Course');
            $teacherName = $this->escape($session->teacher->name ?? 'Unknown Teacher');
            $startedAt   = $this->escape(
                ($session->started_at ?? now())
                    ->setTimezone('Asia/Phnom_Penh')
                    ->format('d M Y, H:i')
            );

            $mode = match ($session->verification_mode ?? 'location') {
                'wifi'  => '📶 School WiFi',
                'both'  => '📍 Location \+ 📶 WiFi',
                default => '📍 GPS Location',
            };

            $caption = implode("\n", [
                '📚 *New Attendance Session Started*',
                '',
                "🏫 *Course:* {$courseName}",
                "👨‍🏫 *Teacher:* {$teacherName}",
                "🆔 *Session ID:* \#{$session->id}",
                "🕐 *Started At:* {$startedAt}",
                "🔒 *Verification:* {$mode}",
                '',
                '📱 Students, scan this QR code to check in\!',
            ]);

            $pngBytes = $this->generateQrPng($token);

            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->attach('photo', $pngBytes, "session_{$session->id}_qr.png")
                ->post("{$this->apiUrl}{$this->botToken}/sendPhoto", [
                    'chat_id'    => $this->chatId,
                    'caption'    => $caption,
                    'parse_mode' => 'MarkdownV2',
                ]);

            if (!$response->successful()) {
                Log::warning("TelegramService: sendPhoto failed — {$response->body()}");
            } else {
                Log::info("TelegramService: QR photo sent for session #{$session->id}.");
            }

        } catch (\Throwable $e) {
            // Never let Telegram errors disrupt the actual session flow
            Log::error("TelegramService: sendSessionQR exception — {$e->getMessage()}");
        }
    }

    /**
     * Send a check-in notification to the configured Telegram chat after a student checks in.
     *
     * Called immediately after AttendanceCheckInController::checkIn() records attendance.
     */
    public function sendCheckInNotification(AttendanceRecord $record): void
    {
        if (!$this->isConfigured()) {
            Log::warning('TelegramService: sendCheckInNotification skipped — Telegram credentials not set. Set TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID.');
            return;
        }

        try {
            $record->loadMissing(['student:id,name,email', 'session.course:id,name']);

            $name       = $this->escape($record->student->name  ?? 'Unknown');
            $email      = $this->escape($record->student->email ?? '');
            $course     = $this->escape($record->session->course->name ?? 'Unknown Course');
            $sessionId  = $record->session_id;
            $checkedAt  = $this->escape(
                ($record->checked_in_at ?? now())
                    ->setTimezone('Asia/Phnom_Penh')
                    ->format('d M Y, H:i:s')
            );

            $statusLabel = match ($record->status) {
                'present' => '✅ Present',
                'late'    => '⚠️ Late',
                'excused' => '📋 Excused',
                default   => '❌ Absent',
            };

            $message = implode("\n", [
                '✅ *Student Checked In*',
                '',
                "👤 *Name:* {$name}",
                "📧 *Email:* {$email}",
                "📚 *Course:* {$course}",
                "🆔 *Session ID:* \#{$sessionId}",
                "⏰ *Time:* {$checkedAt}",
                "📊 *Status:* {$statusLabel}",
                '🔍 *Method:* QR Scan',
            ]);

            $response = Http::timeout(8)
                ->connectTimeout(5)
                ->post("{$this->apiUrl}{$this->botToken}/sendMessage", [
                    'chat_id'    => $this->chatId,
                    'text'       => $message,
                    'parse_mode' => 'MarkdownV2',
                ]);

            if (!$response->successful()) {
                Log::warning("TelegramService: sendMessage failed — {$response->body()}");
            } else {
                Log::info("TelegramService: Check-in notified for student #{$record->student_id}.");
            }

        } catch (\Throwable $e) {
            Log::error("TelegramService: sendCheckInNotification exception — {$e->getMessage()}");
        }
    }
}

