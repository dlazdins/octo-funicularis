<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('price');
            $table->text( 'description' )->nullable();
            $table->timestamps();
        });

        Schema::create('item_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer( 'item_id' )->unsigned();
            $table->string( 'name' );
            $table->text( 'description' )->nullable();
            $table->string( 'locale' )->index();
            $table->unique( [ 'item_id', 'locale' ], 'item_localized' );
            $table->foreign( 'item_id' )->references( 'id' )->on( 'items' )->onDelete( 'cascade' );
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
        Schema::dropIfExists('items');
        Schema::dropIfExists('item_translations');
    }
}