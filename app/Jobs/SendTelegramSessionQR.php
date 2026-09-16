<?php

namespace App\Jobs;

use App\Models\AttendanceSession;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramSessionQR implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times the job may be attempted.
     * If Telegram is temporarily down, retry up to 3 times.
     */
    public int $tries = 3;

    /**
     * Seconds to wait between retries.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     *
     * @param int    $sessionId   The ID of the newly started attendance session
     * @param string $token       The raw token string to encode into the QR image
     */
    public function __construct(
        public readonly int $sessionId,
        public readonly string $token,
    ) {}

    /**
     * Execute the job.
     * Loads the session fresh from the database and sends the QR photo to Telegram.
     */
    public function handle(TelegramService $telegramService): void
    {
        $session = AttendanceSession::with(['course:id,name', 'teacher:id,name'])
            ->find($this->sessionId);

        if (!$session) {
            return; // Session was deleted before the job ran
        }

        $telegramService->sendSessionQR($session, $this->token);
    }
}
