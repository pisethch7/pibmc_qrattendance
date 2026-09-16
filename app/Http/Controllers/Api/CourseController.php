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
                ->withCount(['students', 'sessions'])
                ->latest()
                ->get();
        } else {
            $courses = $user->enrolledCourses()
                ->with('teacher:id,name,email')
                ->withCount('sessions')
                ->get();
        }

        return response()->json([
            'courses' => $courses,
        ]);
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
            'name' => ['required', 'string', 'max:255'],
            'schedule_info' => ['nullable', 'string', 'max:255'],
        ]);

        $course = Course::create([
            'teacher_id' => $user->id,
            'name' => $validated['name'],
            'schedule_info' => $validated['schedule_info'] ?? null,
        ]);

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $course->loadCount('students'),
        ], 201);
    }

    /**
     * Get specific course with students and active/recent sessions.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        $course->load([
            'teacher:id,name,email',
            'students:id,name,email',
            'sessions' => fn ($query) => $query->latest()->limit(10),
        ])->loadCount(['students', 'sessions']);

        return response()->json([
            'course' => $course,
        ]);
    }
}
