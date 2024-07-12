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
        Schema::create('lead_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('status')->default(false);
            $table->BigInteger('priority')->unsignedBigInteger()->default(0);
            $table->boolean('is_delete')->default(false);
            $table->BigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->BigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->string('color_code')->nullable();
            $table->integer('is_default')->unsignedInteger()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_stages');
    }
};
