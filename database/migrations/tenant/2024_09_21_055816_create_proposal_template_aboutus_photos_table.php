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
        Schema::create('proposal_template_aboutus_photos', function (Blueprint $table) {
            $table->id();
            $table->string('image_icon')->nullable();
            $table->boolean('proposal_template_id')->unsignedBigInteger()->default(0);
            $table->boolean('about_flg')->default(false);
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->boolean('aws_flag')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_template_aboutus_photos');
    }
};
