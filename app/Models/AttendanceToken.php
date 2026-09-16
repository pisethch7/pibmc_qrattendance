<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'token',
        'expires_at',
        'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isRedeemed(): bool
    {
        return !is_null($this->redeemed_at);
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isRedeemed();
    }

    public function markRedeemed(): void
    {
        $this->update(['redeemed_at' => now()]);
    }
}
