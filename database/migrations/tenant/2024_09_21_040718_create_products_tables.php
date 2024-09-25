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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('image_one')->nullable();
            $table->text('image_two')->nullable();
            $table->text('image_three')->nullable();
            $table->string('thumb_image_one')->nullable();
            $table->string('thumb_image_two')->nullable();
            $table->string('thumb_image_three')->nullable();
            $table->boolean('status')->default(false);
            $table->integer('user_id')->unsignedBigInteger()->default(0);
            $table->timestamps();
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->boolean('aws_flag')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_tables');
    }
};
