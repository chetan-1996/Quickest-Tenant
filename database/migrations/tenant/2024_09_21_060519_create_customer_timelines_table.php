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
        Schema::create('customer_timelines', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('estimate_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('assigned_to')->unsignedBigInteger()->default(0);
            $table->bigInteger('customer_id')->unsignedBigInteger()->default(0);
            $table->integer('activity_type')->unsignedInteger()->default(0);
            $table->string('activity_name')->nullable();
            $table->text('activity_notes')->nullable();
            $table->text('internal_remarks')->nullable();
            $table->dateTimeTz('follow_up_datetime', $precision = 0)->nullable();
            $table->string('entry_type')->nullable();
            $table->boolean('is_modified')->default(false);
            $table->boolean('is_follow_up')->default(false);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('created_by')->unsignedBigInteger()->default(0);
            $table->bigInteger('updated_by')->unsignedBigInteger()->default(0);
            $table->string('estimate_version_no')->nullable();
            $table->double('net_amount', 10, 2)->default(0);
            $table->string('activity_estimate_status')->nullable();
            $table->boolean('read_flag')->default(0);
            $table->bigInteger('read_by')->unsignedBigInteger()->default(0);
            $table->timestamp('read_date')->nullable();
            $table->bigInteger('content_id')->unsignedBigInteger()->default(0);
            $table->string('visit_latitude')->nullable();
            $table->string('visit_longitude')->nullable();
            $table->text('visit_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_timelines');
    }
};
