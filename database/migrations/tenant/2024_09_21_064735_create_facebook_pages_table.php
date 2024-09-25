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
        Schema::create('facebook_pages', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('page_id')->unsignedBigInteger()->default(0);
            $table->string('page_name')->nullable();
            $table->BigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->BigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->BigInteger('form_count')->unsignedBigInteger()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_pages');
    }
};
