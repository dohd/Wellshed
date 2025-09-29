<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToBoqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id')->after('id');
            $table->decimal('boq_subtotal', 16,4)->default(0)->after('subtotal');
            $table->decimal('boq_tax', 16,4)->default(0)->after('tax');
            $table->decimal('boq_taxable', 16,4)->default(0)->after('taxable');
            $table->decimal('boq_total', 16,4)->default(0)->after('total');
            // $table->foreign('lead_id')->references('id')->on('leads');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropColumn(['boq_subtotal', 'boq_tax', 'boq_taxable', 'boq_total','lead_id']);
        });
    }
}
