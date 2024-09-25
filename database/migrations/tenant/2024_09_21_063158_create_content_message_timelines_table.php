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
        Schema::create('content_message_timelines', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id')->unsignedBigInteger()->default(0);
            $table->integer('activity_type')->unsignedInteger()->default(0);
            $table->string('activity_name')->nullable();
            $table->text('activity_notes')->nullable();
            $table->text('internal_remarks')->nullable();
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('created_by')->unsignedBigInteger()->default(0);
            $table->bigInteger('updated_by')->unsignedBigInteger()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_message_timelines');
    }
};
