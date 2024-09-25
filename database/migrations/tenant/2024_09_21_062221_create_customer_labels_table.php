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
        Schema::create('customer_labels', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('label_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_labels');
    }
};
