<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoundResource;
use App\Models\Round;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoundController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $rounds = Round::query()
            ->whereHas('players', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['course', 'scores.hole', 'players'])
            ->when($request->course_id, fn ($q) => $q->where('course_id', $request->course_id))
            ->when($request->from, fn ($q) => $q->where('played_at', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->where('played_at', '<=', $request->to))
            ->latest('played_at')
            ->paginate(min($request->integer('per_page', 15), 100));

        return RoundResource::collection($rounds);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Round::class);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'played_at' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $round = $request->user()->rounds()->create($validated);
        $round->players()->attach($request->user()->id);

        return (new RoundResource($round->load(['course', 'scores.hole', 'players'])))->response()->setStatusCode(201);
    }

    public function show(Round $round): RoundResource
    {
        $this->authorize('view', $round);

        return new RoundResource($round->load(['course', 'scores.hole', 'players']));
    }

    public function update(Request $request, Round $round): RoundResource
    {
        $this->authorize('update', $round);

        $validated = $request->validate([
            'course_id' => 'sometimes|required|exists:courses,id',
            'played_at' => 'sometimes|required|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $round->update($validated);

        return new RoundResource($round->load(['course', 'scores.hole', 'players']));
    }

    public function destroy(Round $round): Response
    {
        $this->authorize('delete', $round);

        $round->delete();

        return response()->noContent();
    }
}
