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
        Schema::create('smosa_feedbacks', function (Blueprint $table) {
            $table->id();

            // 1. Overall experience
            $table->string('overall_experience');

            // 2. Event experience ratings (Excellent / Good / Fair / Poor)
            $table->string('rating_event_organization');
            $table->string('rating_communication');
            $table->string('rating_venue_setup');
            $table->string('rating_programme_activities');
            $table->string('rating_food_refreshments');
            $table->string('rating_entertainment');
            $table->string('rating_guest_experience');
            $table->string('rating_time_management');

            // 3-6. Open feedback
            $table->text('best_part')->nullable();
            $table->text('improvements')->nullable();
            $table->text('future_suggestions')->nullable();
            $table->text('other_comments')->nullable();

            // 7. Future participation
            $table->string('future_participation');
            $table->json('activities_interest')->nullable();
            $table->string('activities_other')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smosa_feedbacks');
    }
};
