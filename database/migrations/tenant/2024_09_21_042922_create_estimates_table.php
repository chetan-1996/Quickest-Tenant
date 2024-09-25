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
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->longText('customer_address')->nullable();
            $table->bigInteger('customer_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('customer_state_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('company_state_id')->unsignedBigInteger()->default(0);
            $table->string('estimate_no')->nullable();
            $table->string('reference')->nullable();
            $table->date('estimate_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->double('subtotal', 10, 2)->default(0);
            $table->double('total_cgst_amount', 10, 2)->default(0);
            $table->double('total_sgst_amount', 10, 2)->default(0);
            $table->double('total_igst_amount', 10, 2)->default(0);
            $table->float('addless_amount', 5, 2)->default(0);
            $table->double('net_amount', 10, 2)->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('sales_person_id')->unsignedBigInteger()->default(0);
            $table->boolean('item_rate_are')->default(true);
            $table->longText('customer_notes')->nullable();
            $table->longText('term_condition')->nullable();
            $table->string('addless_title')->nullable();
            $table->enum('status', ['Draft', 'Sent', 'Inprogress', 'Accept', 'Decline']);
            $table->text('est_cover_page_title')->nullable();
            $table->text('est_cover_page_content')->nullable();
            $table->text('est_cover_page_footer_one')->nullable();
            $table->text('est_cover_page_footer_two')->nullable();
            $table->text('est_aboutus_title')->nullable();
            $table->text('est_aboutus_content')->nullable();
            $table->text('est_term_condition_title')->nullable();
            $table->text('est_term_condition_content')->nullable();
            $table->string('product_id')->nullable();
            $table->boolean('pdf_cover_page_flg')->default(false);
            $table->boolean('pdf_about_us_flg')->default(false);
            $table->boolean('pdf_product_flg')->default(false);
            $table->boolean('pdf_est_flg')->default(false);
            $table->boolean('pdf_terms_flg')->default(false);
            $table->boolean('pdf_testimonial_flg')->default(false);
            $table->boolean('pdf_thank_you_flg')->default(false);
            $table->text('est_cover_page_title_div')->nullable();
            $table->text('est_cover_page_content_div')->nullable();
            $table->text('est_cover_page_footer_one_div')->nullable();
            $table->text('est_cover_page_footer_two_div')->nullable();
            $table->text('est_aboutus_title_div')->nullable();
            $table->text('est_aboutus_content_div')->nullable();
            $table->text('est_term_condition_title_div')->nullable();
            $table->text('est_term_condition_content_div')->nullable();
            $table->bigInteger('testimonial_id')->unsignedBigInteger()->default(0);
            $table->string('azumuth',255)->nullable();
            $table->unsignedInteger('tilt')->default(0);
            $table->unsignedInteger('no_of_panel')->default(0);
            $table->unsignedInteger('panel_wattage')->default(0);
            $table->integer('term_condition_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('estimate_version')->unsignedBigInteger()->default(0);
            $table->string('est_currency_name')->nullable();
            $table->unsignedBigInteger('est_currency_id')->default(0);
            $table->boolean('aws_flag')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
