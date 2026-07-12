<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_requests', function (Blueprint $table) {
    
            $table->integer('bon_number')->nullable();
            
            $table->string('bon_number_formatted')->nullable()->unique();
         
        });
    }

    public function down(): void
    {
        Schema::table('item_requests', function (Blueprint $table) {
            $table->dropColumn(['bon_number', 'bon_number_formatted']);
        });
    }
};