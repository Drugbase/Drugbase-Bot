<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDrugsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('user_id')->nullable();
            $table->string('street_name')->nullable();
            $table->string('city')->nullable();
            $table->string('active_substance')->nullable();
            $table->string('symbol')->nullable();
            $table->string('state')->nullable();
            $table->string('appearance')->nullable();
            $table->string('color')->nullable();
            $table->string('inscription')->nullable();
            $table->string('shape')->nullable();
            $table->string('weight')->nullable();
            $table->string('weight_active')->nullable();
            $table->string('description')->nullable();
            $table->string('negative_effect')->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('confirm')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drugs');
    }
}
