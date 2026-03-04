<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoundPlayerController extends Controller
{
    public function store(Request $request, Round $round): JsonResponse
    {
        $this->authorize('managePlayers', $round);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($round->players()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User is already a player in this round.'], 422);
        }

        $round->players()->attach($user->id);

        return response()->json(['data' => new UserResource($user)], 201);
    }

    public function destroy(Round $round, User $user): Response
    {
        $this->authorize('managePlayers', $round);

        if ($user->id === $round->user_id) {
            abort(422, 'Cannot remove the round creator from the player list.');
        }

        $round->players()->detach($user->id);

        return response()->noContent();
    }
}
