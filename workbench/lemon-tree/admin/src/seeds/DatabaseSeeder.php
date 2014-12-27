<?php namespace LemonTree;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		\Eloquent::unguard();

		$this->call('LemonTree\GroupTableSeeder');

		$this->call('LemonTree\UserTableSeeder');
	}

}
