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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile_no')->string()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('company_name')->nullable();
            $table->timestamps();
            $table->rememberToken();
            $table->json('data')->nullable();
            $table->string('social_id')->nullable();
            $table->string('social_type')->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->nullable();
            $table->integer('country_id')->unsignedBigInteger()->default(0);
            $table->integer('state_id')->unsignedBigInteger()->default(0);
            $table->integer('city_id')->unsignedBigInteger()->default(0);
            $table->integer('company_category')->unsignedBigInteger()->default(0);
            $table->string('website_link')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('profile_icon')->nullable();
            $table->enum('status', ['Pending','New', 'Approved','Rejected'])->default('New');
            $table->integer('company_id')->nullable();
            $table->enum('user_role', ['0', '1']);
            $table->integer('role_id')->nullable();
            $table->text('permissions')->nullable();
            $table->integer('user_id')->default(0);
            $table->string('is_owner')->nullable();
            $table->string('otp')->nullable();
            $table->string('facebook_url',255)->nullable();
            $table->string('twitter_url',255)->nullable();
            $table->string('instagram_url',255)->nullable();
            $table->string('linkedin_url',255)->nullable();
            $table->string('device_key')->nullable();
            $table->string('mobile_device_key')->nullable();
            $table->dateTime('plan_start_date')->default(Carbon::now());
            $table->dateTime('plan_end_date')->default(Carbon::now()->addDays(14));
            $table->integer('remaining_days')->unsigned()->default(14);
            $table->string('city_name')->nullable();
            $table->boolean('customer_show_flg')->default(false);
            $table->boolean('is_accepted_terms_condition')->default(0);
            $table->string('role_name')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->boolean('popupStatus')->default('0');
            $table->boolean('invite_status')->default(0);
            $table->boolean('plan_status')->default(0)->comment('0 for 7 days Trial, 1 for 7 days expired, 2 for 15 days trial, 3 for 15 days trial, 4 for free, 5 for paid');
            $table->boolean('follow_up_note_req_flg')->default(0);
            $table->boolean('weekly_cron_flg')->default(0);
            $table->boolean('monthly_cron_flg')->default(0);
            $table->enum('indiamart_integration', ['Unassigned', 'Round-Robin'])->default('Round-Robin');
            $table->string('assigned_list')->string()->nullable();
            $table->string('facebook_email')->nullable();
            $table->string('facebook_id')->nullable();
            $table->text('facebook_token')->nullable();
            $table->string('facebook_name')->nullable();
            $table->text('facebook_webhook_verify_token')->nullable();
            $table->boolean('aws_flag')->default(0);
            $table->enum('facebook_integration', ['Unassigned', 'Round-Robin'])->default('Round-Robin');
            $table->double('storage_capacity', 10, 2)->default(2);
            $table->string('call_url')->nullable();
            $table->string('gmail_url')->nullable();
            $table->text('whatsapp_url')->nullable();
            $table->string('call_code_url')->nullable();
            $table->string('whatsapp_code_url')->nullable();
            $table->enum('tradeindia_integration', ['Unassigned', 'Round-Robin'])->default('Round-Robin');
            $table->string('domain')->nullable();
            $table->timestamp('email_verified_at')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
