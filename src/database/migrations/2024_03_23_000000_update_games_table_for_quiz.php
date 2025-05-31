<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->text('long_description')->after('description');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->after('image_path');
            $table->integer('time_limit')->comment('in minutes')->after('difficulty');
            $table->integer('question_count')->after('time_limit');
            $table->integer('points_per_question')->default(10)->after('question_count');
            $table->integer('skip_limit')->default(3)->after('points_per_question');
            $table->json('topics')->nullable()->after('skip_limit');
            $table->json('rules')->nullable()->after('topics');
            $table->json('stats')->nullable()->after('rules');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'long_description',
                'difficulty',
                'time_limit',
                'question_count',
                'points_per_question',
                'skip_limit',
                'topics',
                'rules',
                'stats'
            ]);
        });
    }
}; 