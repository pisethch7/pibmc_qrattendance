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
            Log::info('TelegramService: skipped — not configured.');
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

            $response = Http::attach('photo', $pngBytes, "session_{$session->id}_qr.png")
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

            $response = Http::post("{$this->apiUrl}{$this->botToken}/sendMessage", [
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

