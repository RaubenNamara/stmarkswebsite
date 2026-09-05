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
        Schema::table('student_leaderships', function (Blueprint $table) {

            // ✅ DROP OLD COLUMNS (only if they exist)
            if (Schema::hasColumn('student_leaderships', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('student_leaderships', 'position')) {
                $table->dropColumn('position');
            }

            if (Schema::hasColumn('student_leaderships', 'bio')) {
                $table->dropColumn('bio');
            }

            if (Schema::hasColumn('student_leaderships', 'sort_order')) {
                $table->dropColumn('sort_order');
            }

            if (Schema::hasColumn('student_leaderships', 'is_active')) {
                $table->dropColumn('is_active');
            }

            // ✅ ADD NEW COLUMNS (only if not exist)
            if (!Schema::hasColumn('student_leaderships', 'title')) {
                $table->string('title')->after('id');
            }

            if (!Schema::hasColumn('student_leaderships', 'content')) {
                $table->longText('content')->nullable();
            }

            if (!Schema::hasColumn('student_leaderships', 'image_path')) {
                $table->string('image_path')->nullable();
            }

            if (!Schema::hasColumn('student_leaderships', 'video_path')) {
                $table->string('video_path')->nullable();
            }

            if (!Schema::hasColumn('student_leaderships', 'video_link')) {
                $table->string('video_link')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_leaderships', function (Blueprint $table) {

            // remove new columns
            if (Schema::hasColumn('student_leaderships', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('student_leaderships', 'content')) {
                $table->dropColumn('content');
            }

            if (Schema::hasColumn('student_leaderships', 'image_path')) {
                $table->dropColumn('image_path');
            }

            if (Schema::hasColumn('student_leaderships', 'video_path')) {
                $table->dropColumn('video_path');
            }

            if (Schema::hasColumn('student_leaderships', 'video_link')) {
                $table->dropColumn('video_link');
            }

            // restore old columns
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->text('bio')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });
    }
};