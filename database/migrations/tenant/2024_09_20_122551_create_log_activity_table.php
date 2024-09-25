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
        Schema::create('log_activity', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('url');
            $table->string('method');
            $table->string('ip');
            $table->string('agent')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamps();
            $table->text('properties')->nullable();
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('log_id')->unsignedBigInteger()->default(0);
            $table->string('log_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_activity');
    }
};
