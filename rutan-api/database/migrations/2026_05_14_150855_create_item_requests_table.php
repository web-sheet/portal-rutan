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
        Schema::create('item_requests', function (Blueprint $table) {
            $table->id();

            $table->string('employee_name');
            $table->string('division');

            $table->foreignId('item_id')->constrained()->cascadeOnDelete();

            $table->string('item_name');     // snapshot
            $table->string('category');      // snapshot
            $table->integer('stock_requested');

            $table->string('status')->default('pending');
            // pending | approved | rejected

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_requests');
    }
};
