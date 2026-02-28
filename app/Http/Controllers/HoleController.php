<?php

namespace App\Http\Controllers;

use App\Http\Resources\HoleResource;
use App\Models\Course;
use App\Models\Hole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class HoleController extends Controller
{
    public function index(Course $course): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Hole::class);

        return HoleResource::collection($course->holes);
    }

    public function store(Request $request, Course $course): JsonResponse
    {
        $this->authorize('create', Hole::class);

        $validated = $request->validate([
            'number' => 'required|integer|min:1|max:36',
            'par' => 'required|integer|in:3,4,5',
            'distance_feet' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $hole = $course->holes()->create($validated);

        return (new HoleResource($hole))->response()->setStatusCode(201);
    }

    public function show(Course $course, Hole $hole): HoleResource
    {
        $this->authorize('view', $hole);
        $this->ensureHoleBelongsToCourse($course, $hole);

        return new HoleResource($hole);
    }

    public function update(Request $request, Course $course, Hole $hole): HoleResource
    {
        $this->authorize('update', $hole);
        $this->ensureHoleBelongsToCourse($course, $hole);

        $validated = $request->validate([
            'number' => 'sometimes|required|integer|min:1|max:36',
            'par' => 'sometimes|required|integer|in:3,4,5',
            'distance_feet' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $hole->update($validated);

        return new HoleResource($hole);
    }

    public function destroy(Course $course, Hole $hole): Response
    {
        $this->authorize('delete', $hole);
        $this->ensureHoleBelongsToCourse($course, $hole);

        $hole->delete();

        return response()->noContent();
    }

    private function ensureHoleBelongsToCourse(Course $course, Hole $hole): void
    {
        abort_if($hole->course_id !== $course->id, 404);
    }
}
