<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMortgagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mortgages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('uuid')->nullable()->index();
            $table->bigInteger('amo_mortgage_id');
            $table->bigInteger('amo_mortgage_creation_stage_id');
            $table->bigInteger('amo_mortgage_applying_stage_id');
            $table->json('amo_mortgage_before_applying_stage_ids');
            $table->json('amo_mortgage_after_applying_stage_ids');
            $table->bigInteger('amo_mortgage_approved_stage_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mortgages');
    }
}
