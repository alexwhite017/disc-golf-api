<?php

namespace App\Http\Controllers;

use App\Http\Resources\ScoreResource;
use App\Models\Round;
use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScoreController extends Controller
{
    public function store(Request $request, Round $round): JsonResponse
    {
        $this->authorize('create', [Score::class, $round]);

        $validated = $request->validate([
            'hole_id' => 'required|exists:holes,id',
            'strokes' => 'required|integer|min:1|max:99',
        ]);

        $score = $round->scores()->updateOrCreate(
            ['hole_id' => $validated['hole_id']],
            ['strokes' => $validated['strokes']]
        );

        return (new ScoreResource($score->load('hole')))->response()->setStatusCode(201);
    }

    public function destroy(Round $round, Score $score): Response
    {
        $this->authorize('delete', $score);
        abort_if($score->round_id !== $round->id, 404);

        $score->delete();

        return response()->noContent();
    }

    public function update(Request $request, Round $round, Score $score): ScoreResource
    {
        $this->authorize('update', $score);
        abort_if($score->round_id !== $round->id, 404);

        $validated = $request->validate([
            'strokes' => 'required|integer|min:1|max:99',
        ]);

        $score->update($validated);

        return new ScoreResource($score->load('hole'));
    }
}
