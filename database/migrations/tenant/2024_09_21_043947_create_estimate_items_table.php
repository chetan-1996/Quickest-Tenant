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
        Schema::create('estimate_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('estimate_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('item_id')->unsignedBigInteger()->default(0);
            $table->string('item_name')->nullable();
            $table->string('unit_name')->nullable();
            $table->string('hsn_code')->nullable();
            $table->longText('item_description')->nullable();
            $table->float('quantity', 8, 2)->default(0);
            $table->double('price', 10, 2)->default(0);
            $table->float('discount', 8, 2)->default(0);
            $table->boolean('discount_flag')->default(false);
            $table->float('gst_per', 6, 2)->default(0);
            $table->double('cgst_amount', 10, 2)->default(0);
            $table->double('sgst_amount', 10, 2)->default(0);
            $table->double('igst_amount', 10, 2)->default(0);
            $table->double('total', 10, 2)->default(0);
            $table->bigInteger('company_id')->unsignedBigInteger()->default(0);
            $table->bigInteger('user_id')->unsignedBigInteger()->default(0);
            $table->text('technical_specification')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_items');
    }
};
