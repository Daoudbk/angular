<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCytrusTabs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cytrus_tabs', function ($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->string('title');
			$table->string('url');
			$table->boolean('is_active');
			$table->boolean('show_tree');
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
		Schema::drop('cytrus_tabs');
	}

}
