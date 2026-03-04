<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add user_id nullable to scores
        Schema::table('scores', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('round_id')->constrained()->cascadeOnDelete();
        });

        // 2. Backfill scores.user_id from the round's user_id
        DB::table('rounds')->orderBy('id')->chunk(200, function ($rounds) {
            foreach ($rounds as $round) {
                DB::table('scores')
                    ->where('round_id', $round->id)
                    ->update(['user_id' => $round->user_id]);
            }
        });

        // 3. Make user_id non-nullable
        Schema::table('scores', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        // 4. Add new unique (round_id, hole_id, user_id) first, then drop old (round_id, hole_id)
        //    (MySQL requires an index to remain on round_id for the foreign key constraint)
        Schema::table('scores', function (Blueprint $table) {
            $table->unique(['round_id', 'hole_id', 'user_id']);
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->dropUnique(['round_id', 'hole_id']);
        });

        // 5. Backfill round_players: insert creator as player for every existing round
        DB::table('rounds')->orderBy('id')->chunk(200, function ($rounds) {
            $rows = [];
            $now = now();
            foreach ($rounds as $round) {
                $rows[] = [
                    'round_id' => $round->id,
                    'user_id'  => $round->user_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($rows) {
                DB::table('round_players')->insertOrIgnore($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropUnique(['round_id', 'hole_id', 'user_id']);
            $table->unique(['round_id', 'hole_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
