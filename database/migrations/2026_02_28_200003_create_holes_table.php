<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('number')->unsigned();
            $table->tinyInteger('par')->unsigned()->default(3);
            $table->unsignedInteger('distance_feet')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holes');
    }
};
