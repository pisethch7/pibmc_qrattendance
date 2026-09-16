<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'teacher_id',
        'started_at',
        'ends_at',
        'latitude',
        'longitude',
        'radius_meters',
        'verification_mode',
        'require_wifi',
        'wifi_subnet',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meters' => 'integer',
            'require_wifi' => 'boolean',
        ];
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'ends_at' => now(),
        ]);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(AttendanceToken::class, 'session_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }

    public function latestToken(): HasOne
    {
        return $this->hasOne(AttendanceToken::class, 'session_id')->latestOfMany();
    }
}
