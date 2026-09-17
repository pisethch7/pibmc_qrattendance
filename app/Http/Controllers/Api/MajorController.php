<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    /**
     * List all majors (optionally filtered by department).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Major::with('department:id,name')->withCount('courses')->orderBy('name');

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        return response()->json(['majors' => $query->get()]);
    }

    /**
     * Create a major under a department.
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->isTeacher()) {
            return response()->json(['message' => 'Only teachers can manage majors.'], 403);
        }

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name'          => ['required', 'string', 'max:255'],
        ]);

        $major = Major::create($validated);

        return response()->json([
            'message' => 'Major created successfully.',
            'major'   => $major->load('department:id,name')->loadCount('courses'),
        ], 201);
    }

    /**
     * Update a major's name or department.
     */
    public function update(Request $request, Major $major): JsonResponse
    {
        if (!$request->user()->isTeacher()) {
            return response()->json(['message' => 'Only teachers can manage majors.'], 403);
        }

        $validated = $request->validate([
            'department_id' => ['sometimes', 'exists:departments,id'],
            'name'          => ['required', 'string', 'max:255'],
        ]);

        $major->update($validated);

        return response()->json([
            'message' => 'Major updated.',
            'major'   => $major->fresh()->load('department:id,name')->loadCount('courses'),
        ]);
    }

    /**
     * Delete a major (courses become unassigned — major_id set null).
     */
    public function destroy(Request $request, Major $major): JsonResponse
    {
        if (!$request->user()->isTeacher()) {
            return response()->json(['message' => 'Only teachers can manage majors.'], 403);
        }

        $major->delete();

        return response()->json(['message' => 'Major deleted.']);
    }
}
