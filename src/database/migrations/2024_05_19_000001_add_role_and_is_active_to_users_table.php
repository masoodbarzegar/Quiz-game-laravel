<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // For SQLite, we need to recreate the table
            Schema::table('users', function (Blueprint $table) {
                // Drop foreign key constraints first
                $table->dropForeign(['created_by']);
                $table->dropForeign(['approved_by']);
                $table->dropForeign(['rejected_by']);
            });

            // Create a new table with the desired schema
            Schema::create('users_new', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['manager', 'corrector', 'general', 'user'])->default('general');
                $table->boolean('is_active')->default(true);
                $table->rememberToken();
                $table->timestamps();
            });

            // Copy data from old table to new table
            DB::statement('INSERT INTO users_new SELECT id, name, email, email_verified_at, password, "general" as role, 1 as is_active, remember_token, created_at, updated_at FROM users');

            // Drop old table and rename new one
            Schema::drop('users');
            Schema::rename('users_new', 'users');

            // Recreate foreign key constraints
            Schema::table('questions', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('approved_by')->references('id')->on('users');
                $table->foreign('rejected_by')->references('id')->on('users');
            });
        } else {
            // For other databases, use the original migration
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['manager', 'corrector', 'general', 'user'])->default('general')->after('password');
                $table->boolean('is_active')->default(true)->after('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // For SQLite, recreate the original table
            Schema::table('users', function (Blueprint $table) {
                // Drop foreign key constraints first
                $table->dropForeign(['created_by']);
                $table->dropForeign(['approved_by']);
                $table->dropForeign(['rejected_by']);
            });

            // Create a new table with the original schema
            Schema::create('users_new', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });

            // Copy data from old table to new table
            DB::statement('INSERT INTO users_new SELECT id, name, email, email_verified_at, password, remember_token, created_at, updated_at FROM users');

            // Drop old table and rename new one
            Schema::drop('users');
            Schema::rename('users_new', 'users');

            // Recreate foreign key constraints
            Schema::table('questions', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users');
                $table->foreign('approved_by')->references('id')->on('users');
                $table->foreign('rejected_by')->references('id')->on('users');
            });
        } else {
            // For other databases, use the original rollback
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['role', 'is_active']);
            });
        }
    }
}; 