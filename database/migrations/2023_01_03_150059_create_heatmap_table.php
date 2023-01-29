<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heatmap', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('harga');
            $table->float('lat');
            $table->float('long');
            $table->string('type');
            $table->integer('area');
            $table->text('desc')->default('');
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
        Schema::dropIfExists('heatmap');
    }
};
