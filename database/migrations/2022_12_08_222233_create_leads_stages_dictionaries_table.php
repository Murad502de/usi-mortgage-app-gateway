<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsStagesDictionariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_stages_dictionaries', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('amo_id');
            $table->string('name');
            $table->foreignId('leads_pipelines_dictionary_id')->nullable()->constrained()->cascadeOnDelete()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads_stages_dictionaries');
    }
}
