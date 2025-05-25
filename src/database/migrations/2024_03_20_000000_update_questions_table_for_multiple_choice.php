<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            // Drop the old correct_answer column
            $table->dropColumn('correct_answer');

            // Add new columns for multiple choice
            $table->json('choices')->after('question_text');
            $table->unsignedTinyInteger('correct_choice')->after('choices')->comment('1-based index of the correct choice');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            // Remove the multiple choice columns
            $table->dropColumn(['choices', 'correct_choice']);

            // Restore the old correct_answer column
            $table->text('correct_answer')->after('question_text');
        });
    }
}; 