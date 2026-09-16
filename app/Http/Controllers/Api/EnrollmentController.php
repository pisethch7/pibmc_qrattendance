<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    /**
     * Enroll a student in a course.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTeacher()) {
            return response()->json(['message' => 'Only teachers can manage enrollments.'], 403);
        }

        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $course = Course::findOrFail($validated['course_id']);

        // Verify teacher owns course
        if ($course->teacher_id !== $user->id) {
            return response()->json(['message' => 'You do not teach this course.'], 403);
        }

        $student = User::findOrFail($validated['student_id']);
        if (!$student->isStudent()) {
            throw ValidationException::withMessages([
                'student_id' => ['The selected user is not a student.'],
            ]);
        }

        $exists = Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'student_id' => ['This student is already enrolled in the course.'],
            ]);
        }

        $enrollment = Enrollment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
        ]);

        return response()->json([
            'message' => 'Student enrolled successfully.',
            'enrollment' => $enrollment->load('student:id,name,email'),
        ], 201);
    }

    /**
     * Remove student from course.
     */
    public function destroy(Request $request, int $courseId, int $studentId): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTeacher()) {
            return response()->json(['message' => 'Only teachers can manage enrollments.'], 403);
        }

        $course = Course::findOrFail($courseId);
        if ($course->teacher_id !== $user->id) {
            return response()->json(['message' => 'You do not teach this course.'], 403);
        }

        $deleted = Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->delete();

        return response()->json([
            'message' => $deleted ? 'Student removed from course.' : 'Enrollment not found.',
        ]);
    }

    /**
     * List all students in system (for teacher enrollment selection).
     */
    public function students(): JsonResponse
    {
        $students = User::where('role', 'student')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'students' => $students,
        ]);
    }
}
