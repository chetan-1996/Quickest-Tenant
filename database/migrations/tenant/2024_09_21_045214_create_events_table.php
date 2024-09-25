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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('estimate_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->text('notes')->nullable();
            $table->string('event_type')->nullable();
            $table->boolean('next_follow_up')->default(false);
            $table->timestamp('start_date')->useCurrent();
            $table->timestamp('end_date')->useCurrent();
            $table->string('class_name')->nullable();
            $table->bigInteger('read_by')->unsignedBigInteger()->default(0);
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
