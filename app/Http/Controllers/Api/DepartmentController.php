<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * List all departments with their majors and course counts.
     */
    public function index(): JsonResponse
    {
        $departments = Department::with(['majors' => function ($q) {
            $q->withCount('courses')->orderBy('name');
        }])->withCount('majors')->orderBy('name')->get();

        return response()->json(['departments' => $departments]);
    }

    /**
     * Create a new department (teachers only).
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->isTeacher()) {
            return response()->json(['message' => 'Only teachers can manage departments.'], 403);
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $department = Department::create($validated);

        return response()->json([
            'message'    => 'Department created successfully.',
            'department' => $department->loadCount('majors'),
        ], 201);
    }

    /**
     * Update a department.
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        if (!$request->user()->isTeacher()) {
            return response()->json(['message' => 'Only teachers can manage departments.'], 403);
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:departments,name,' . $department->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $department->update($validated);

        return response()->json([
            'message'    => 'Department updated.',
            'department' => $department->fresh()->loadCount('majors'),
        ]);
    }

    /**
     * Delete a department (cascades to majors, nullifies courses).
     */
    public function destroy(Request $request, Department $department): JsonResponse
    {
        if (!$request->user()->isTeacher()) {
            return response()->json(['message' => 'Only teachers can manage departments.'], 403);
        }

        $department->delete();

        return response()->json(['message' => 'Department deleted.']);
    }
}
