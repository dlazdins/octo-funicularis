<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCullmnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->integer('phone')->after('email');
            $table->string('address')->after('phone');
            $table->string('post_code')->after('address');
            $table->string('country')->after('post_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('phone');
            $table->dropColumn('address');
            $table->dropColumn('post_code');
            $table->dropColumn('country');
        });
    }
}
