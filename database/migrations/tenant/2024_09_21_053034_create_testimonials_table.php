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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_name_one')->nullable();
            $table->text('description_one')->nullable();
            $table->float('rating_one', 3, 2)->default(0.00);
            $table->text('image_one')->nullable();
            $table->string('client_name_two')->nullable();
            $table->text('description_two')->nullable();
            $table->float('rating_two', 3, 2)->default(0.00);
            $table->text('image_two')->nullable();
            $table->string('client_name_three')->nullable();
            $table->text('description_three')->nullable();
            $table->float('rating_three', 3, 2)->default(0.00);
            $table->text('image_three')->nullable();
            $table->boolean('status')->default(false);
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('aws_flag')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
