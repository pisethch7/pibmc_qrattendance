<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Classroom coordinates (Phnom Penh center: 11.5564, 104.9282)
        $classroomLat = 11.5564000;
        $classroomLon = 104.9282000;

        // Teacher
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@pibmc.edu.kh'],
            [
                'name' => 'Prof. Sokha Men',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ]
        );

        // Students
        $studentsData = [
            ['name' => 'Dara Rath', 'email' => 'student1@pibmc.edu.kh'],
            ['name' => 'Sophea Chan', 'email' => 'student2@pibmc.edu.kh'],
            ['name' => 'Visal Meas', 'email' => 'student3@pibmc.edu.kh'],
            ['name' => 'Bopha Chea', 'email' => 'student4@pibmc.edu.kh'],
            ['name' => 'Vannak Heng', 'email' => 'student5@pibmc.edu.kh'],
        ];

        $students = [];
        foreach ($studentsData as $data) {
            $students[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => 'student',
                ]
            );
        }

        // Courses
        $course1 = Course::firstOrCreate(
            ['name' => 'CS301 - Web & Mobile Cloud Applications', 'teacher_id' => $teacher->id],
            ['schedule_info' => 'Mon / Wed 08:30 AM - 10:30 AM (Room 402)']
        );

        $course2 = Course::firstOrCreate(
            ['name' => 'CS204 - Database Systems & Architecture', 'teacher_id' => $teacher->id],
            ['schedule_info' => 'Tue / Thu 01:30 PM - 03:30 PM (Lab 3)']
        );

        // Enroll students
        foreach ([$students[0], $students[1], $students[2], $students[3]] as $s) {
            Enrollment::firstOrCreate(['course_id' => $course1->id, 'student_id' => $s->id]);
        }

        foreach ([$students[0], $students[1], $students[4]] as $s) {
            Enrollment::firstOrCreate(['course_id' => $course2->id, 'student_id' => $s->id]);
        }

        // Historical completed session for Course 1 (2 days ago)
        $pastSession = AttendanceSession::firstOrCreate(
            ['course_id' => $course1->id, 'started_at' => now()->subDays(2)->setTime(8, 30)],
            [
                'teacher_id' => $teacher->id,
                'ends_at' => now()->subDays(2)->setTime(10, 30),
                'latitude' => $classroomLat,
                'longitude' => $classroomLon,
                'radius_meters' => 100,
                'status' => 'closed',
            ]
        );

        // Records for past session
        AttendanceRecord::firstOrCreate(
            ['session_id' => $pastSession->id, 'student_id' => $students[0]->id],
            [
                'status' => 'present',
                'checked_in_at' => now()->subDays(2)->setTime(8, 33),
                'method' => 'qr',
                'latitude' => $classroomLat + 0.0001,
                'longitude' => $classroomLon + 0.0001,
            ]
        );

        AttendanceRecord::firstOrCreate(
            ['session_id' => $pastSession->id, 'student_id' => $students[1]->id],
            [
                'status' => 'late',
                'checked_in_at' => now()->subDays(2)->setTime(8, 52),
                'method' => 'qr',
                'latitude' => $classroomLat,
                'longitude' => $classroomLon,
            ]
        );

        AttendanceRecord::firstOrCreate(
            ['session_id' => $pastSession->id, 'student_id' => $students[2]->id],
            [
                'status' => 'excused',
                'checked_in_at' => now()->subDays(2)->setTime(9, 00),
                'method' => 'manual',
                'latitude' => null,
                'longitude' => null,
            ]
        );

        // Active open session for testing today!
        $activeSession = AttendanceSession::create([
            'course_id' => $course1->id,
            'teacher_id' => $teacher->id,
            'started_at' => now(),
            'latitude' => $classroomLat,
            'longitude' => $classroomLon,
            'radius_meters' => 120,
            'status' => 'open',
        ]);

        $attendanceService = app(AttendanceService::class);
        $attendanceService->generateToken($activeSession, 30);
    }
}
