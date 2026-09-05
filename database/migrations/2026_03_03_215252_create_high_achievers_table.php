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
    Schema::create('high_achievers', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('photo');
        $table->string('year');
        $table->string('exam'); // UNEB, UACE, UCE
        $table->string('division')->nullable();
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('high_achievers');
    }
};
