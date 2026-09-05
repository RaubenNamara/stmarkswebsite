<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('co_curriculars', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->longText('content')->nullable();
        $table->string('image')->nullable();
        $table->string('video')->nullable(); // can store video path OR youtube link
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('co_curriculars');
    }
};
