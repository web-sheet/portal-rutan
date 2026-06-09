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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_request_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('item_name');
            $table->integer('quantity');

            $table->enum('type', ['IN', 'OUT']); // stok masuk / keluar

            $table->integer('stock_before');
            $table->integer('stock_after');

            $table->string('action_by')->nullable(); // siapa approve

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
