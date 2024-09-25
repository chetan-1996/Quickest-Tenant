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
        Schema::create('content_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('path')->nullable();
            $table->boolean('status')->default(false);
            $table->integer('user_id')->unsignedBigInteger()->default(0);
            $table->integer('company_id')->unsignedBigInteger()->default(0);
            $table->timestamps();
            $table->boolean('aws_flag')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_files');
    }
};
