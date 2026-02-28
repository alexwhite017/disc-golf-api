<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $roundStats = DB::table('rounds')
            ->join('scores', 'rounds.id', '=', 'scores.round_id')
            ->join('holes', 'scores.hole_id', '=', 'holes.id')
            ->where('rounds.user_id', $user->id)
            ->select(
                DB::raw('COUNT(DISTINCT rounds.id) as rounds_played'),
                DB::raw('COUNT(scores.id) as holes_played'),
                DB::raw('SUM(scores.strokes) as total_strokes'),
                DB::raw('SUM(holes.par) as total_par'),
            )
            ->first();

        $bestRound = DB::table('rounds')
            ->join('scores', 'rounds.id', '=', 'scores.round_id')
            ->join('holes', 'scores.hole_id', '=', 'holes.id')
            ->join('courses', 'rounds.course_id', '=', 'courses.id')
            ->where('rounds.user_id', $user->id)
            ->select(
                'rounds.id',
                'rounds.played_at',
                'courses.name as course_name',
                DB::raw('SUM(scores.strokes) as total_strokes'),
                DB::raw('SUM(scores.strokes) - SUM(holes.par) as score_vs_par'),
            )
            ->groupBy('rounds.id', 'rounds.played_at', 'courses.name')
            ->orderBy('score_vs_par')
            ->first();

        $scoreDistribution = DB::table('scores')
            ->join('rounds', 'scores.round_id', '=', 'rounds.id')
            ->join('holes', 'scores.hole_id', '=', 'holes.id')
            ->where('rounds.user_id', $user->id)
            ->select(
                DB::raw('SUM(CASE WHEN scores.strokes <= holes.par - 2 THEN 1 ELSE 0 END) as eagles_or_better'),
                DB::raw('SUM(CASE WHEN scores.strokes = holes.par - 1 THEN 1 ELSE 0 END) as birdies'),
                DB::raw('SUM(CASE WHEN scores.strokes = holes.par THEN 1 ELSE 0 END) as pars'),
                DB::raw('SUM(CASE WHEN scores.strokes = holes.par + 1 THEN 1 ELSE 0 END) as bogeys'),
                DB::raw('SUM(CASE WHEN scores.strokes >= holes.par + 2 THEN 1 ELSE 0 END) as double_bogeys_or_worse'),
            )
            ->first();

        $favoriteCourse = DB::table('rounds')
            ->join('courses', 'rounds.course_id', '=', 'courses.id')
            ->where('rounds.user_id', $user->id)
            ->select(
                'courses.id',
                'courses.name',
                DB::raw('COUNT(rounds.id) as rounds_played'),
            )
            ->groupBy('courses.id', 'courses.name')
            ->orderByDesc('rounds_played')
            ->first();

        $discsInBag = $user->discs()->where('is_in_bag', true)->count();

        $roundsPlayed = (int) ($roundStats->rounds_played ?? 0);
        $totalStrokes = (int) ($roundStats->total_strokes ?? 0);
        $totalPar = (int) ($roundStats->total_par ?? 0);

        return response()->json([
            'rounds_played' => $roundsPlayed,
            'holes_played' => (int) ($roundStats->holes_played ?? 0),
            'avg_score_vs_par' => $roundsPlayed > 0
                ? round(($totalStrokes - $totalPar) / $roundsPlayed, 2)
                : null,
            'best_round' => $bestRound ? [
                'id' => $bestRound->id,
                'played_at' => $bestRound->played_at,
                'course' => $bestRound->course_name,
                'total_strokes' => (int) $bestRound->total_strokes,
                'score_vs_par' => (int) $bestRound->score_vs_par,
            ] : null,
            'score_distribution' => [
                'eagles_or_better' => (int) ($scoreDistribution->eagles_or_better ?? 0),
                'birdies' => (int) ($scoreDistribution->birdies ?? 0),
                'pars' => (int) ($scoreDistribution->pars ?? 0),
                'bogeys' => (int) ($scoreDistribution->bogeys ?? 0),
                'double_bogeys_or_worse' => (int) ($scoreDistribution->double_bogeys_or_worse ?? 0),
            ],
            'favorite_course' => $favoriteCourse ? [
                'id' => $favoriteCourse->id,
                'name' => $favoriteCourse->name,
                'rounds_played' => (int) $favoriteCourse->rounds_played,
            ] : null,
            'discs_in_bag' => $discsInBag,
        ]);
    }
}
