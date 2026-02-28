<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $courses = Course::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->state, fn ($q) => $q->where('state', $request->state))
            ->when($request->city, fn ($q) => $q->where('city', $request->city))
            ->paginate($request->integer('per_page', 15));

        return CourseResource::collection($courses);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $course = Course::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return (new CourseResource($course->load('holes')))->response()->setStatusCode(201);
    }

    public function show(Course $course): CourseResource
    {
        $this->authorize('view', $course);

        return new CourseResource($course->load('holes'));
    }

    public function update(Request $request, Course $course): CourseResource
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $course->update($validated);

        return new CourseResource($course->load('holes'));
    }

    public function destroy(Course $course): Response
    {
        $this->authorize('delete', $course);

        $course->delete();

        return response()->noContent();
    }
}
