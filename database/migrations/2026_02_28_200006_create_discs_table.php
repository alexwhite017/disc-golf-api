<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('brand');
            $table->string('name');
            $table->enum('type', ['driver', 'fairway_driver', 'mid_range', 'putter']);
            $table->decimal('weight_grams', 5, 1)->nullable();
            $table->string('color')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_in_bag')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discs');
    }
};
