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
        Schema::create('telescope_entries', function (Blueprint $table) {
            $table->bigInteger('sequence');
            $table->string('uuid')->nullable();
            $table->string('batch_id')->nullable();
            $table->string('family_hash')->nullable();
            $table->tinyInteger('should_display_on_index')->nullable();
            $table->string('type')->nullable();
            $table->text('content')->nullable();
            $table->date('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telescope_entries');
    }
};
