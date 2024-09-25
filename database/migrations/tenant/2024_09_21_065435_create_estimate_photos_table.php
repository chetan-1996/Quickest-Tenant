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
        Schema::create('estimate_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estimate_id')->default(0);
            $table->unsignedBigInteger('product_flag')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->string('image_one')->nullable();
            $table->string('image_two')->nullable();
            $table->string('image_three')->nullable();
            $table->string('thumb_image_one')->nullable();
            $table->string('thumb_image_two')->nullable();
            $table->string('thumb_image_three')->nullable();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedBigInteger('comapny_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_photos');
    }
};
