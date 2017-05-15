<?php

/**
 * This file is part of laravel-quota
 *
 * (c) David Faith <david@projectmentor.org>
 *
 * Full copyright and license information is available
 * in the LICENSE file distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * This is the quota log database migration.
 *
 * NOTE: currently supports daily period only.
 * TODO: REFACTOR to support multiple period types.
 *
 * @See PerodicQuota.php
 *
 */
class CreateQuotalogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotalog', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->string('connection');   //@See config/quota.php
            $table->integer('hits')->default(0);
            $table->integer('misses')->default(0);

            $table->timestamps();

            $table->unique(['date', 'connection']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('quotalog');
    }
}
