<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->boolean('require_wifi')->default(false)->after('radius_meters');
            $table->string('wifi_subnet')->nullable()->after('require_wifi');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn(['require_wifi', 'wifi_subnet']);
        });
    }
};
