<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Round;
use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index(): JsonResponse
    {
        $leaderboard = DB::table('users')
            ->join('rounds', 'users.id', '=', 'rounds.user_id')
            ->join('scores', 'rounds.id', '=', 'scores.round_id')
            ->join('holes', 'scores.hole_id', '=', 'holes.id')
            ->select(
                'users.id as user_id',
                'users.name',
                DB::raw('COUNT(DISTINCT rounds.id) as rounds_played'),
                DB::raw('SUM(scores.strokes) as total_strokes'),
                DB::raw('SUM(holes.par) as total_par'),
                DB::raw('SUM(scores.strokes) - SUM(holes.par) as total_vs_par'),
                DB::raw('ROUND((SUM(scores.strokes) - SUM(holes.par)) / COUNT(DISTINCT rounds.id), 2) as avg_vs_par_per_round')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('avg_vs_par_per_round')
            ->get();

        return response()->json(['data' => $leaderboard]);
    }

    public function show(Course $course): JsonResponse
    {
        $leaderboard = DB::table('users')
            ->join('rounds', 'users.id', '=', 'rounds.user_id')
            ->join('scores', 'rounds.id', '=', 'scores.round_id')
            ->join('holes', 'scores.hole_id', '=', 'holes.id')
            ->where('rounds.course_id', $course->id)
            ->select(
                'users.id as user_id',
                'users.name',
                DB::raw('COUNT(DISTINCT rounds.id) as rounds_played'),
                DB::raw('SUM(scores.strokes) as total_strokes'),
                DB::raw('SUM(holes.par) as total_par'),
                DB::raw('SUM(scores.strokes) - SUM(holes.par) as total_vs_par'),
                DB::raw('ROUND((SUM(scores.strokes) - SUM(holes.par)) / COUNT(DISTINCT rounds.id), 2) as avg_vs_par_per_round')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('avg_vs_par_per_round')
            ->get();

        return response()->json([
            'course' => [
                'id' => $course->id,
                'name' => $course->name,
            ],
            'data' => $leaderboard,
        ]);
    }
}
