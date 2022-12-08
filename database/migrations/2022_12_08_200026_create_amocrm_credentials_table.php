<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmocrmCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amocrm_credentials', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('subdomain')->nullable();
            $table->text('access_token')->nullable();
            $table->string('redirect_uri')->nullable();
            $table->string('token_type')->nullable();
            $table->text('refresh_token')->nullable();
            $table->bigInteger('when_expires')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amocrm_credentials');
    }
}
