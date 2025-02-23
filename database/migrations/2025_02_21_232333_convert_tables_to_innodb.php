<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $tables = [
        'users',
        'products',
        'orders',
        'order_items',
        'payments',
        'subscriptions',
        'carts',
        'cart_items'
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE $table ENGINE = InnoDB");
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE $table ENGINE = MyISAM");
        }
    }
};