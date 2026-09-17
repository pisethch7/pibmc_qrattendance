<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add username (used instead of email for login)
            $table->string('username')->unique()->nullable()->after('name');
            // Add sex for students
            $table->enum('sex', ['male', 'female'])->nullable()->after('role');
            // Make email nullable (teachers may have email, students don't need it)
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'sex']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
