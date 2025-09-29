<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesAgentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_agent_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sale_agent_id');
            $table->foreign('sale_agent_id')
                ->references('id')
                ->on('sales_agents')
                ->onDelete('cascade');
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable(); // ["Sales", "Cold-calling", ...]
            $table->json('experience')->nullable(); // [{company, role, from, to, achievements:[]}, ...]
            $table->json('education')->nullable(); // [{school, award, year}, ...]
            $table->string('cv_path')->nullable(); // storage path to uploaded CV
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('availability')->nullable(); // full-time / part-time / gig
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->json('preferred_categories')->nullable();
            $table->json('extra')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_agent_profiles');
    }
}
