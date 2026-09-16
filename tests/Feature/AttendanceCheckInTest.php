<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\AttendanceToken;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected User $student;
    protected Course $course;
    protected AttendanceSession $session;
    protected AttendanceService $attendanceService;

    // Phnom Penh coordinates for realistic testing
    protected float $classroomLat = 11.5564000;
    protected float $classroomLon = 104.9282000;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attendanceService = app(AttendanceService::class);

        // Create teacher
        $this->teacher = User::create([
            'name' => 'Dr. Alice Smith',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
        ]);

        // Create student
        $this->student = User::create([
            'name' => 'Bob Johnson',
            'email' => 'student@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        // Create course
        $this->course = Course::create([
            'teacher_id' => $this->teacher->id,
            'name' => 'CS301 - Web Development',
            'schedule_info' => 'Mon/Wed 9:00 AM',
        ]);

        // Enroll student
        Enrollment::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
        ]);

        // Start session (radius 100 meters)
        $this->session = AttendanceSession::create([
            'course_id' => $this->course->id,
            'teacher_id' => $this->teacher->id,
            'started_at' => now(),
            'latitude' => $this->classroomLat,
            'longitude' => $this->classroomLon,
            'radius_meters' => 100,
            'status' => 'open',
        ]);
    }

    public function test_student_can_check_in_with_valid_token_and_coordinates(): void
    {
        $token = $this->attendanceService->generateToken($this->session);

        // Student is 25 meters away from classroom center
        // ~0.0002 deg latitude is ~22 meters
        $studentLat = $this->classroomLat + 0.0002;
        $studentLon = $this->classroomLon;

        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $studentLat,
                'longitude' => $studentLon,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'student_id' => $this->student->id,
                    'session_id' => $this->session->id,
                    'status' => 'present',
                    'method' => 'qr',
                ],
            ]);

        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $this->student->id,
            'session_id' => $this->session->id,
            'status' => 'present',
            'method' => 'qr',
        ]);

        // Token must now be marked redeemed
        $token->refresh();
        $this->assertTrue($token->isRedeemed());
    }

    public function test_expired_token_is_rejected(): void
    {
        $token = AttendanceToken::create([
            'session_id' => $this->session->id,
            'token' => 'expired-token-12345678901234567890',
            'expires_at' => now()->subSeconds(30),
            'redeemed_at' => null,
        ]);

        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_out_of_range_coordinates_are_rejected(): void
    {
        $token = $this->attendanceService->generateToken($this->session);

        // Location ~2.2 km away from classroom
        $studentLat = $this->classroomLat + 0.02;
        $studentLon = $this->classroomLon + 0.02;

        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $studentLat,
                'longitude' => $studentLon,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['coordinates']);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_redeemed_token_cannot_be_reused_by_another_student(): void
    {
        $secondStudent = User::create([
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        Enrollment::create([
            'course_id' => $this->course->id,
            'student_id' => $secondStudent->id,
        ]);

        $token = $this->attendanceService->generateToken($this->session);

        // First student checks in
        $firstResponse = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
            ]);
        $firstResponse->assertStatus(200);

        // Second student tries to use the same token (e.g. shared screenshot)
        $secondResponse = $this->actingAs($secondStudent, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
            ]);

        $secondResponse->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_unenrolled_student_cannot_check_in(): void
    {
        $unenrolledStudent = User::create([
            'name' => 'Unenrolled Dan',
            'email' => 'dan@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $token = $this->attendanceService->generateToken($this->session);

        $response = $this->actingAs($unenrolledStudent, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['enrollment']);
    }

    public function test_student_cannot_check_in_twice_to_same_session(): void
    {
        $token1 = $this->attendanceService->generateToken($this->session);

        $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token1->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
            ])
            ->assertStatus(200);

        // Now teacher rotates the token
        $token2 = $this->attendanceService->generateToken($this->session);

        // Student tries to check in again
        $secondAttempt = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token2->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
            ]);

        $secondAttempt->assertStatus(422)
            ->assertJsonValidationErrors(['attendance']);
    }

    public function test_check_in_rejected_when_session_is_closed(): void
    {
        $token = $this->attendanceService->generateToken($this->session);
        $this->session->close();

        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session']);
    }

    public function test_teacher_can_manually_override_attendance_record(): void
    {
        // Check in as present
        $record = AttendanceRecord::create([
            'session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'status' => 'present',
            'checked_in_at' => now(),
            'method' => 'qr',
            'latitude' => $this->classroomLat,
            'longitude' => $this->classroomLon,
        ]);

        // Teacher overrides to excused
        $response = $this->actingAs($this->teacher, 'sanctum')
            ->patchJson("/api/attendance/records/{$record->id}", [
                'status' => 'excused',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'record' => [
                    'id' => $record->id,
                    'status' => 'excused',
                    'method' => 'manual',
                ],
            ]);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $record->id,
            'status' => 'excused',
            'method' => 'manual',
        ]);
    }

    public function test_check_in_rejected_when_outside_school_wifi_subnet(): void
    {
        // Enforce school WiFi subnet 192.168.1.0/24 on this session
        $this->session->update([
            'verification_mode' => 'wifi',
            'require_wifi' => true,
            'wifi_subnet' => '192.168.1.0/24',
        ]);

        $token = $this->attendanceService->generateToken($this->session);

        // Student sends from home / mobile carrier IP (e.g. 203.0.113.88)
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
                'simulate_ip' => '203.0.113.88',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['network']);
    }

    public function test_check_in_accepted_when_request_originates_from_school_wifi(): void
    {
        // Enforce school WiFi subnet 192.168.1.0/24
        $this->session->update([
            'verification_mode' => 'wifi',
            'require_wifi' => true,
            'wifi_subnet' => '192.168.1.0/24',
        ]);

        $token = $this->attendanceService->generateToken($this->session);

        // Student device connected to school WiFi router (192.168.1.45)
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token->token,
                'latitude' => $this->classroomLat,
                'longitude' => $this->classroomLon,
                'simulate_ip' => '192.168.1.45',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'student_id' => $this->student->id,
                    'status' => 'present',
                ],
            ]);
    }

    public function test_wifi_verify_endpoint_returns_network_presence_status(): void
    {
        $this->session->update([
            'require_wifi' => true,
            'wifi_subnet' => '192.168.1.0/24',
        ]);

        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson("/api/attendance/wifi-verify?session_id={$this->session->id}&simulate_ip=192.168.1.77");

        $response->assertStatus(200)
            ->assertJson([
                'is_campus_network' => true,
                'require_wifi' => true,
            ]);
    }

    public function test_teacher_can_start_session_with_wifi_option_and_student_can_check_in_without_gps(): void
    {
        // Teacher creates session with verification_mode = 'wifi'
        $createResponse = $this->actingAs($this->teacher, 'sanctum')
            ->postJson('/api/attendance/sessions', [
                'course_id' => $this->course->id,
                'verification_mode' => 'wifi',
                'wifi_subnet' => '10.0.0.0/16',
            ]);

        $createResponse->assertStatus(201)
            ->assertJson([
                'message' => 'Attendance session started.',
            ]);

        $token = $createResponse->json('token');

        // Student checks in from school WiFi without any GPS coordinates
        $checkInResponse = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/attendance/check-in', [
                'token' => $token,
                'simulate_ip' => '10.0.5.22',
            ]);

        $checkInResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'student_id' => $this->student->id,
                    'status' => 'present',
                ],
            ]);
    }
}
