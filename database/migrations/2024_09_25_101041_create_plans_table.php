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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('price');
            $table->integer('users_limit')->nullable();
            $table->integer('estimate_limit')->nullable();
            $table->integer('yearly_price')->nullable();
            $table->boolean('status')->default('0');
            $table->boolean('isDefault')->default('0');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('plan_history', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 255)->nullable();
            $table->unsignedBigInteger('plan_id')->default(0);
            $table->integer('user_limit')->nullable();
            $table->integer('estimate_limit')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
        Schema::dropIfExists('plan_history');
    }
};
