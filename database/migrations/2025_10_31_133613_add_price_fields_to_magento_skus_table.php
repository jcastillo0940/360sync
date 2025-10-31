<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('magento_skus', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('sku');
            $table->decimal('special_price', 10, 2)->nullable()->after('price');
            $table->date('special_from_date')->nullable()->after('special_price');
            $table->date('special_to_date')->nullable()->after('special_from_date');
        });
    }

    public function down()
    {
        Schema::table('magento_skus', function (Blueprint $table) {
            $table->dropColumn(['price', 'special_price', 'special_from_date', 'special_to_date']);
        });
    }
};
