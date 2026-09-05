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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('page_url');
            $table->string('page_type')->nullable(); // news, post, etc.
            $table->unsignedBigInteger('page_id')->nullable(); // news_id, post_id, etc.
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->date('view_date');
            $table->timestamps();

            $table->index(['page_type', 'page_id', 'view_date']);
            $table->index('view_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
