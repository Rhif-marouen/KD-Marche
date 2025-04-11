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
        Schema::table('stock_history', function (Blueprint $table) {
            Schema::table('stock_history', function (Blueprint $table) {
                $table->integer('old_stock')->after('product_id');
                $table->integer('new_stock')->after('old_stock');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_history', function (Blueprint $table) {
            //
        });
    }
};
