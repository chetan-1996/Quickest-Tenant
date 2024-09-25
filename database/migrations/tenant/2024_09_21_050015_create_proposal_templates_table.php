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
        Schema::create('proposal_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name',50)->nullable();
            $table->string('theme_color_one',10)->nullable();
            $table->string('theme_color_two',10)->nullable();
            $table->string('header_logo',205)->nullable();
            $table->string('cover_img',255)->nullable();
            $table->string('logo_dimension_one',10)->nullable();
            $table->string('logo_dimension_img',10)->nullable();
            $table->text('cover_title')->nullable();
            $table->text('cover_content')->nullable();
            $table->text('cover_footer_one')->nullable();
            $table->text('cover_footer_two')->nullable();
            $table->string('aboutas_img',255)->nullable();
            $table->string('aboutas_logo_dimension',10)->nullable();
            $table->text('aboutas_title')->nullable();
            $table->text('aboutas_content')->nullable();
            $table->string('est_title',20)->nullable();
            $table->string('est_logo_dimension',10)->nullable();
            $table->string('item_table_no',50)->nullable();
            $table->string('item_table_item',50)->nullable();
            $table->string('item_table_description',50)->nullable();
            $table->string('item_table_hsn',50)->nullable();
            $table->string('item_table_qty',50)->nullable();
            $table->string('item_table_rate',50)->nullable();
            $table->string('item_table_discount',50)->nullable();
            $table->string('item_table_cgst',50)->nullable();
            $table->string('item_table_sgst',50)->nullable();
            $table->string('item_table_igst',50)->nullable();
            $table->string('item_table_total',50)->nullable();
            $table->string('est_bank_label',255)->nullable();
            $table->text('est_bank_details')->nullable();
            $table->string('est_term_condition_lable',20)->nullable();
            $table->text('est_term_condition_details')->nullable();
            $table->string('est_signature_lable',20)->nullable();
            $table->string('est_signature_img',255)->nullable();
            $table->boolean('est_item_no_flg')->default(0);
            $table->boolean('est_item_description_flg')->default(0);
            $table->text('product_title')->nullable();
            $table->text('product_content')->nullable();
            $table->text('terms_title')->nullable();
            $table->text('terms_content')->nullable();
            $table->text('testimonials_title')->nullable();
            $table->text('testimonials_content')->nullable();
            $table->bigInteger('company_id')->unsignedBigInterger()->default(0);
            $table->bigInteger('user_id')->unsignedBigInterger()->default(0);
            $table->timestamps();
            $table->unsignedInteger('header_logo_left')->default(0);
            $table->unsignedInteger('header_logo_top')->default(0);
            $table->unsignedInteger('header_logo_size')->default(0);
            $table->float('page_top_margin', 8, 1)->default(15.5);
            $table->string('thank_you_img',255)->nullable();
            $table->string('theme_header_color',255)->default("#152e42");
            $table->string('theme_footer_color',255)->default("#ffbc00");
            $table->boolean('item_number_flag')->default(false);
            $table->boolean('item_hsn_flag')->default(false);
            $table->boolean('item_discount_flag')->default(false);
            $table->boolean('item_cgst_flag')->default(false);
            $table->boolean('item_sgst_flag')->default(false);
            $table->boolean('item_igst_flag')->default(false);
            $table->text('est_customer_notes_details')->nullable();
            $table->boolean('item_icon_flag')->default(false);
            $table->string('item_table_icon',255)->nullable();
            $table->integer('term_condition_id')->unsignedBigInteger()->default(0);
            $table->boolean('new_pdf_flag')->default(0);
            $table->boolean('cover_page_flg')->default(1);
            $table->boolean('about_us_flg')->default(1);
            $table->integer('thank_you_flg')->unsignedInteger()->default(1);
            $table->boolean('photo_position_flg')->default(0);
            $table->boolean('aws_flag')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_templates');
    }
};
