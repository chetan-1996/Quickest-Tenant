<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_histroy', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('lead_id')->nullable();
            $table->BigInteger('user_id')->nullable();
            $table->string('company_id')->nullable();
            $table->string('email')->nullable();
            $table->string('domain')->nullable();
            $table->dateTime('insert_date')->nullable()->default(Carbon::now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_histroy');
    }
};
