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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->enum('customer_type', ['Business', 'Individual'])->default('Business');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->nullable();
            $table->integer('country_id')->unsignedBigInteger()->default(0);
            $table->integer('state_id')->unsignedBigInteger()->default(0);
            $table->integer('city_id')->unsignedBigInteger()->default(0);
            $table->string('city_name')->nullable();
            $table->string('profile_icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(false);
            $table->integer('customer_category_id')->unsignedBigInteger()->default(0);
            $table->integer('customer_lead_id')->unsignedBigInteger()->default(0);
            $table->string('gst_no')->nullable();
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->string('company_name',255)->nullable();
            $table->bigInteger('assigned_to_user')->unsignedBigInteger()->default(0);
            $table->boolean('some_day_flg')->default(0);
            $table->string('country_code')->nullable();
            $table->boolean('new_lead_flag')->default(0);
            $table->string('whatsapp_no')->nullable();
            $table->string('whatsapp_country_code')->nullable();
            $table->string('currency_name')->nullable();
            $table->string('currency_code')->nullable();
            $table->integer('phone_no_country_id')->unsignedBigInteger()->default(0);
            $table->integer('whatsapp_no_country_id')->unsignedBigInteger()->default(0);
            $table->integer('currency_name_country_id')->unsignedBigInteger()->default(0);
            $table->string('leadgen_id')->nullable();
            $table->bigInteger('lead_stage_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('lost_reason_id')->unsignedBigInteger()->default(0);
            $table->string('others_reason')->nullable();
            $table->bigInteger('generated_id')->unsignedBigInteger()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
