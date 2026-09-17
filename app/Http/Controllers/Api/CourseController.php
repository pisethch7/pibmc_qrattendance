<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * List courses for current user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isTeacher()) {
            $courses = Course::where('teacher_id', $user->id)
                ->with('major:id,name,department_id', 'major.department:id,name')
                ->withCount(['students', 'sessions'])
                ->latest()
                ->get();
        } else {
            $courses = $user->enrolledCourses()
                ->with('teacher:id,name,email', 'major:id,name,department_id', 'major.department:id,name')
                ->withCount('sessions')
                ->get();
        }

        return response()->json(['courses' => $courses]);
    }

    /**
     * Create a new course (teachers only).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTeacher()) {
            return response()->json(['message' => 'Only teachers can create courses.'], 403);
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'schedule_info' => ['nullable', 'string', 'max:255'],
            'major_id'      => ['nullable', 'exists:majors,id'],
        ]);

        $course = Course::create([
            'teacher_id'    => $user->id,
            'major_id'      => $validated['major_id'] ?? null,
            'name'          => $validated['name'],
            'schedule_info' => $validated['schedule_info'] ?? null,
        ]);

        return response()->json([
            'message' => 'Course created successfully.',
            'course'  => $course->load('major:id,name,department_id', 'major.department:id,name')
                               ->loadCount('students'),
        ], 201);
    }

    /**
     * Update a course (teacher who owns it only).
     */
    public function update(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTeacher() || $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'schedule_info' => ['nullable', 'string', 'max:255'],
            'major_id'      => ['nullable', 'exists:majors,id'],
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Course updated.',
            'course'  => $course->fresh()
                               ->load('major:id,name,department_id', 'major.department:id,name')
                               ->loadCount('students'),
        ]);
    }

    /**
     * Delete a course (teacher who owns it only).
     */
    public function destroy(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTeacher() || $course->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted.']);
    }

    /**
     * Get specific course with students and active/recent sessions.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        $course->load([
            'teacher:id,name,email',
            'major:id,name,department_id',
            'major.department:id,name',
            'students:id,name,username,email,sex,device_id,device_name,device_registered_at',
            'sessions' => fn ($query) => $query->latest()->limit(10),
        ])->loadCount(['students', 'sessions']);

        return response()->json(['course' => $course]);
    }
}
