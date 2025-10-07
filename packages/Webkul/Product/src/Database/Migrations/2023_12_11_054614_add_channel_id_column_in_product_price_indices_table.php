<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tablePrefix = DB::getTablePrefix();
        $driver = DB::getDriverName();

        Schema::table('product_price_indices', function (Blueprint $table) use ($tablePrefix, $driver) {
            // SQLite doesn't support dropping foreign keys by name
            // So we skip this for SQLite (in-memory testing)
            if ($driver !== 'sqlite') {
                $table->dropForeign($tablePrefix.'product_price_indices_product_id_foreign');
                $table->dropForeign($tablePrefix.'product_price_indices_customer_group_id_foreign');
                $table->dropUnique($tablePrefix.'product_price_indices_product_id_customer_group_id_unique');
            }

            $table->integer('channel_id')->unsigned()->default(1)->after('customer_group_id');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');

            if ($driver !== 'sqlite') {
                $table->unique(['product_id', 'customer_group_id', 'channel_id'], 'price_indices_product_id_customer_group_id_channel_id_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tablePrefix = DB::getTablePrefix();
        $driver = DB::getDriverName();

        Schema::table('product_price_indices', function (Blueprint $table) use ($tablePrefix, $driver) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['customer_group_id']);
            $table->dropForeign(['channel_id']);
            
            if ($driver !== 'sqlite') {
                $table->dropUnique('price_indices_product_id_customer_group_id_channel_id_unique');

                $table->foreign('customer_group_id', $tablePrefix.'product_price_indices_customer_group_id_foreign')->references('id')->on('customer_groups');
                $table->foreign('product_id', $tablePrefix.'product_price_indices_product_id_foreign')->references('id')->on('products');
                $table->unique(['product_id', 'customer_group_id'], $tablePrefix.'product_price_indices_product_id_customer_group_id_unique');
            }
            
            $table->dropColumn('channel_id');
        });
    }
};
