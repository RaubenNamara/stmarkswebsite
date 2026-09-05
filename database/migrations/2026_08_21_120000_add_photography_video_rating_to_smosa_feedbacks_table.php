<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smosa_feedbacks', function (Blueprint $table) {
            $table->string('rating_photography_video')->nullable()->after('rating_time_management');
        });
    }

    public function down(): void
    {
        Schema::table('smosa_feedbacks', function (Blueprint $table) {
            $table->dropColumn('rating_photography_video');
        });
    }
};
