<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\AttendanceToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * Default token validity in seconds.
     */
    public const DEFAULT_VALIDITY_SECONDS = 3600; // 1 hour — token stays valid for the full class period

    /**
     * Default minutes after session start to be considered late.
     */
    public const LATE_THRESHOLD_MINUTES = 15;

    /**
     * Generate a new short-lived attendance token for a session.
     * Invalidates any previous unredeemed tokens for this session.
     */
    public function generateToken(AttendanceSession $session, int $validitySeconds = self::DEFAULT_VALIDITY_SECONDS): AttendanceToken
    {
        if (!$session->isOpen()) {
            throw ValidationException::withMessages([
                'session' => ['Cannot generate token for a closed session.'],
            ]);
        }

        // Invalidate active unredeemed tokens by expiring them
        AttendanceToken::where('session_id', $session->id)
            ->whereNull('redeemed_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        // Generate cryptographically secure token
        $tokenString = Str::random(48);

        return AttendanceToken::create([
            'session_id' => $session->id,
            'token' => $tokenString,
            'expires_at' => now()->addSeconds($validitySeconds),
            'redeemed_at' => null,
        ]);
    }

    /**
     * Calculate geographical distance in meters between two coordinates using Haversine formula.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Process student check-in via scanned token and coordinates.
     *
     * Validates in exact required order:
     * 1. Token exists, session is open, and token is not expired.
     * 2. Token has not already been redeemed (one-time use).
     * 3. Submitted GPS coordinates are within allowed radius.
     * 4. Optional IP range restriction (if configured).
     * 5. Student is enrolled in the course.
     * 6. Student has not already checked in for this session.
     *
     * @throws ValidationException
     */
    public function checkIn(
        User $student,
        string $tokenString,
        ?float $latitude,
        ?float $longitude,
        ?string $clientIp = null
    ): AttendanceRecord {
        // 1. Token existence and expiration check
        $token = AttendanceToken::with('session.course')->where('token', $tokenString)->first();

        if (!$token) {
            $this->logRejectedAttempt($student, 'invalid_token', 'Invalid attendance token submitted.');
            throw ValidationException::withMessages([
                'token' => ['Invalid or unknown token.'],
            ]);
        }

        $session = $token->session;

        if (!$session || !$session->isOpen()) {
            $this->logRejectedAttempt($student, 'session_closed', 'Attendance session is already closed.');
            throw ValidationException::withMessages([
                'session' => ['The attendance session is closed.'],
            ]);
        }

        if ($token->expires_at->isPast()) {
            $this->logRejectedAttempt($student, 'token_expired', "Token {$tokenString} expired at {$token->expires_at}.");
            throw ValidationException::withMessages([
                'token' => ['Token has expired. Please scan the current code on the screen.'],
            ]);
        }

        // 2. One-time use check (reject duplicate submissions / shared screenshots)
        if ($token->isRedeemed()) {
            $this->logRejectedAttempt($student, 'token_already_redeemed', "Token {$tokenString} already redeemed at {$token->redeemed_at}.");
            throw ValidationException::withMessages([
                'token' => ['Token has already been redeemed. Please scan the refreshed code.'],
            ]);
        }

        $verificationMode = $session->verification_mode;
        if (empty($verificationMode)) {
            $verificationMode = $session->require_wifi ? 'wifi' : 'location';
        }

        // 3. Option 1: Geofence GPS check (required when verification_mode is 'location' or 'both')
        if ($verificationMode === 'location' || $verificationMode === 'both') {
            if ($latitude === null || $longitude === null) {
                $this->logRejectedAttempt($student, 'missing_gps', 'Check-in attempted without GPS coordinates.');
                throw ValidationException::withMessages([
                    'coordinates' => ['GPS coordinates are required to verify classroom presence.'],
                ]);
            }

            if ($session->latitude !== null && $session->longitude !== null) {
                $distance = $this->calculateDistance(
                    (float) $session->latitude,
                    (float) $session->longitude,
                    $latitude,
                    $longitude
                );

                if ($distance > $session->radius_meters) {
                    $distanceMeters = round($distance);
                    $this->logRejectedAttempt(
                        $student,
                        'out_of_range',
                        "Student location ({$latitude}, {$longitude}) is {$distanceMeters}m away (allowed {$session->radius_meters}m)."
                    );
                    throw ValidationException::withMessages([
                        'coordinates' => [
                            "Outside classroom area ({$distanceMeters}m away, allowed radius is {$session->radius_meters}m)."
                        ],
                    ]);
                }
            }
        }

        // 4. Option 2: School WiFi / local subnet verification (required ONLY when verification_mode is 'wifi' or 'both')
        if ($verificationMode === 'wifi' || $verificationMode === 'both') {
            $allowedSubnets = !empty($session->wifi_subnet)
                ? array_filter(array_map('trim', explode(',', $session->wifi_subnet)))
                : config('attendance.school_subnets', []);

            if (!$clientIp || !$this->isIpInSubnets($clientIp, $allowedSubnets)) {
                $this->logRejectedAttempt(
                    $student,
                    'wifi_unauthorized',
                    "Client IP {$clientIp} is outside school WiFi network."
                );
                throw ValidationException::withMessages([
                    'network' => [
                        "School WiFi Required: Your connection originates from an external network (IP: {$clientIp}). Please connect to the classroom or campus WiFi to verify your presence."
                    ],
                ]);
            }
        }

        // 5. Course enrollment check
        $isEnrolled = $session->course->students()
            ->where('users.id', $student->id)
            ->exists();

        if (!$isEnrolled) {
            $this->logRejectedAttempt($student, 'not_enrolled', "Student is not enrolled in course {$session->course->id}.");
            throw ValidationException::withMessages([
                'enrollment' => ['You are not enrolled in this course.'],
            ]);
        }

        // 6. Already checked in check
        $alreadyCheckedIn = AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyCheckedIn) {
            $this->logRejectedAttempt($student, 'already_checked_in', "Student {$student->id} has already checked in.");
            throw ValidationException::withMessages([
                'attendance' => ['You have already checked in for this session.'],
            ]);
        }

        // All checks passed -> Mark token as redeemed
        $token->markRedeemed();

        // Determine status: present vs late based on session start time
        $now = now();
        $isLate = $now->diffInMinutes($session->started_at) > self::LATE_THRESHOLD_MINUTES;
        $status = $isLate ? 'late' : 'present';

        return AttendanceRecord::create([
            'session_id' => $session->id,
            'student_id' => $student->id,
            'status' => $status,
            'checked_in_at' => $now,
            'method' => 'qr',
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /**
     * Check whether an IP address belongs to any of the allowed subnets or IP list.
     */
    public function isIpInSubnets(string $ip, array $subnets): bool
    {
        foreach ($subnets as $subnet) {
            if ($this->ipMatchesSubnet($ip, $subnet)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if client IP matches a subnet CIDR (e.g. 192.168.1.0/24) or exact IP.
     */
    public function ipMatchesSubnet(string $ip, string $range): bool
    {
        $ip = trim($ip);
        $range = trim($range);

        if ($ip === $range) {
            return true;
        }

        // Localhost loopback IPv4/IPv6 compatibility
        if (($ip === '127.0.0.1' || $ip === '::1') && ($range === '127.0.0.1' || $range === '::1')) {
            return true;
        }

        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range, 2);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = -1 << (32 - (int) $bits);
        $subnetLong &= $mask;

        return ($ipLong & $mask) === $subnetLong;
    }

    /**
     * Verify client WiFi connection status against school network subnets.
     */
    public function verifyWifi(string $clientIp, ?AttendanceSession $session = null): array
    {
        $requireWifi = $session ? ($session->require_wifi || config('attendance.default_require_wifi', false)) : true;
        $allowedSubnets = ($session && !empty($session->wifi_subnet))
            ? array_filter(array_map('trim', explode(',', $session->wifi_subnet)))
            : config('attendance.school_subnets', []);

        $isMatch = $this->isIpInSubnets($clientIp, $allowedSubnets);

        return [
            'ip' => $clientIp,
            'is_campus_network' => $isMatch,
            'require_wifi' => $requireWifi,
            'allowed_subnets' => $allowedSubnets,
            'message' => $isMatch
                ? 'Connected to verified school campus WiFi network.'
                : 'Warning: Outside school campus network. Cellular or home IP detected.',
        ];
    }

    /**
     * Log rejected check-in attempts for teacher audit and security monitoring.
     */
    protected function logRejectedAttempt(User $user, string $reason, string $details): void
    {
        Log::warning("Attendance check-in rejected [{$reason}] for user #{$user->id} ({$user->name}): {$details}");
    }
}
