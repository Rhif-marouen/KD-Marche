<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stock_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); 
            $table->integer('quantity')->nullable();
            $table->enum('type', ['in', 'out']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_history');
    }
};