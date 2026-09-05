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
        // Drop old columns safely
        if (Schema::hasTable('fee_structures')) {

            if (Schema::hasColumn('fee_structures', 'class')) {
                Schema::table('fee_structures', function (Blueprint $table) {
                    $table->dropColumn('class');
                });
            }

            if (Schema::hasColumn('fee_structures', 'term')) {
                Schema::table('fee_structures', function (Blueprint $table) {
                    $table->dropColumn('term');
                });
            }

            if (Schema::hasColumn('fee_structures', 'tuition')) {
                Schema::table('fee_structures', function (Blueprint $table) {
                    $table->dropColumn('tuition');
                });
            }

            if (Schema::hasColumn('fee_structures', 'boarding')) {
                Schema::table('fee_structures', function (Blueprint $table) {
                    $table->dropColumn('boarding');
                });
            }

            if (Schema::hasColumn('fee_structures', 'development')) {
                Schema::table('fee_structures', function (Blueprint $table) {
                    $table->dropColumn('development');
                });
            }

            if (Schema::hasColumn('fee_structures', 'other_charges')) {
                Schema::table('fee_structures', function (Blueprint $table) {
                    $table->dropColumn('other_charges');
                });
            }

            // Add new columns safely
            Schema::table('fee_structures', function (Blueprint $table) {

                if (!Schema::hasColumn('fee_structures', 'title')) {
                    $table->string('title')->after('id');
                }

                if (!Schema::hasColumn('fee_structures', 'file_path')) {
                    $table->string('file_path')->after('title');
                }

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {

            if (Schema::hasColumn('fee_structures', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('fee_structures', 'file_path')) {
                $table->dropColumn('file_path');
            }

        });
    }
};