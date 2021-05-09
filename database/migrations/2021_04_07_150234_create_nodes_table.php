<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('host', 30);
            $table->string('status', 30)->nullable();
            $table->string('version', 30)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('region', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->unsignedBigInteger('height')->nullable();
            $table->unsignedBigInteger('proposals')->nullable();
            $table->unsignedBigInteger('relays')->nullable();
            $table->unsignedFloat('speed', 16)->nullable();
            $table->unsignedBigInteger('uptime')->nullable();
            $table->unsignedBigInteger('ping')->nullable();
            $table->text('response')->nullable();
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
        Schema::dropIfExists('nodes');
    }
}
