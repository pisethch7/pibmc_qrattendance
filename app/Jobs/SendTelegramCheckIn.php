<?php

namespace App\Jobs;

use App\Models\AttendanceRecord;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramCheckIn implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Seconds to wait between retries.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     *
     * @param int $recordId  The ID of the newly created attendance record
     */
    public function __construct(
        public readonly int $recordId,
    ) {}

    /**
     * Execute the job.
     * Loads the record fresh and sends the check-in notification to Telegram.
     */
    public function handle(TelegramService $telegramService): void
    {
        $record = AttendanceRecord::with(['student:id,name,email', 'session.course:id,name'])
            ->find($this->recordId);

        if (!$record) {
            return; // Record was deleted before the job ran
        }

        $telegramService->sendCheckInNotification($record);
    }
}
