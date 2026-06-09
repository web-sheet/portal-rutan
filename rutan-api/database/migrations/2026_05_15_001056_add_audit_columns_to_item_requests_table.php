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
        Schema::table('item_requests', function (Blueprint $table) {

            $table->timestamp('requested_at')->nullable();

            $table->timestamp('approved_kaur_at')->nullable();
            $table->timestamp('approved_kasi_at')->nullable();

            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->string('approved_kaur_by')->nullable();
            $table->string('approved_kasi_by')->nullable();

            $table->string('rejected_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_requests', function (Blueprint $table) {
            //
        });
    }
};
