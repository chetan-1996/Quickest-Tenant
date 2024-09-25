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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sortname')->nullable();
            $table->string('phonecode')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(false);
            $table->integer('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->string('currency_name')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('mobile_length')->nullable();
            $table->timestamps();
        });
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('country_id');
            $table->text('description')->nullable();
            $table->boolean('status')->default(false);
            $table->integer('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->timestamps();
        });
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('state_id');
            $table->text('description')->nullable();
            $table->boolean('status')->default(false);
            $table->integer('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('countries');
        Schema::drop('states');
        Schema::drop('cities');
    }
};
