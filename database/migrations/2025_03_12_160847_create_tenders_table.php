<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('title');
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('tid')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->text('team_member_ids')->nullable();
            $table->date('date')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('site_visit_date')->nullable();
            $table->string('consultant')->nullable();
            $table->string('bid_bond_processed')->nullable();
            $table->decimal('bid_bond_amount', 30,2)->nullable()->default(0);
            $table->decimal('amount', 30,2)->nullable()->default(0);
            $table->enum('tender_stages', ['open','negotiation','won','cancelled','lost'])->default('open');
            $table->enum('organization_type', ['private','government'])->default('private');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ins');
            $table->timestamps();
        });
        // DB::table('permissions')->insert([
        //     [
        //         'name' => 'manage-tender',
        //         'display_name' => 'Tender Manage Permission',
        //         'module_id' => 3,
        //         'sort' => 0,
        //         'status' => 1,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'name' => 'create-tender',
        //         'display_name' => 'Tender Create Permission',
        //         'module_id' => 3,
        //         'sort' => 0,
        //         'status' => 1,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'name' => 'edit-tender',
        //         'display_name' => 'Tender Edit Permission',
        //         'module_id' => 3,
        //         'sort' => 0,
        //         'status' => 1,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'name' => 'delete-tender',
        //         'display_name' => 'Tender Delete Permission',
        //         'module_id' => 3,
        //         'sort' => 0,
        //         'status' => 1,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        // ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenders');
    }
}
