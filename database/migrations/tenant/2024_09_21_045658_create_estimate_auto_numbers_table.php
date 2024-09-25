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
        Schema::create('estimate_auto_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('estimate_prefix')->nullable();
            $table->bigInteger('estimate_next_no')->unsignedBigInteger()->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->string('estimate_next_no')->change();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_auto_numbers');
    }
};
