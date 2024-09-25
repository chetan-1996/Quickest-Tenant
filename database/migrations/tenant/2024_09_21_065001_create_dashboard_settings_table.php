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
        Schema::create('dashboard_settings', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('permission_id')->unsignedBigInteger()->default(0);
            $table->BigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->BigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->boolean('is_primary')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_settings');
    }
};
