<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePromoTypeInPromotionalCodes extends Migration
{
    public function up()
    {
        Schema::table('promotional_codes', function (Blueprint $table) {

            DB::statement("ALTER TABLE rose_promotional_codes MODIFY COLUMN promo_type ENUM('specific_products', 'product_categories', 'description_promo') DEFAULT 'product_categories'");

            $table->text('description_promo')->nullable()->after('promo_type');
        });
    }

    public function down()
    {

        Schema::table('promotional_codes', function (Blueprint $table) {

            DB::statement("ALTER TABLE rose_promotional_codes MODIFY COLUMN promo_type ENUM('specific_products', 'product_categories') DEFAULT 'product_categories'");

            $table->dropColumn('description_promo');
        });
    }
}
