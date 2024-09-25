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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('item_type', ['Goods', 'Service']);
            $table->integer('unit_id')->unsignedBigInteger()->default(0);
            $table->string('hsn_code')->nullable();
            $table->enum('tax_preference', ['Taxable', 'Non-Taxable']);
            $table->float('inter_state')->default(0.00);
            $table->float('intra_state')->default(0.00);
            $table->boolean('sales_flag')->default(false);
            $table->boolean('purchase_flag')->default(false);
            $table->decimal('sale_price', 10, 2)->default(0.00);
            $table->decimal('cost_price', 10, 2)->default(0.00);
            $table->boolean('status')->default(false);
            $table->integer('user_id')->unsignedBigInteger()->default(0);
            $table->timestamps();
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->string('image_icon')->nullable();
            $table->double('item_discount', 10, 2)->default(0);
            $table->integer('item_discount_flag')->unsignedInteger()->default(0);
            $table->text('technical_specification')->nullable();
            $table->boolean('aws_flag')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
