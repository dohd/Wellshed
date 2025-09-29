<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappParamsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('graph_api_url')->nullable();
            $table->string('meta_developer_app_id')->nullable();
            $table->string('whatsapp_business_config_id')->nullable();
            $table->string('whatsapp_business_account_id')->nullable();
            $table->string('whatsapp_phone_no_id')->nullable();
            $table->string('whatsapp_access_token', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'graph_api_url',
                'meta_developer_app_id',
                'whatsapp_business_config_id',
                'whatsapp_business_account_id', 
                'whatsapp_phone_no_id', 
                'whatsapp_access_token',
            ]);
        });
    }
}
