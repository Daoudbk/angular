<?php namespace LemonTree;

use Illuminate\Database\Seeder;
use Illuminate\Database\Facades\DB;

class UserTableSeeder extends Seeder {

	public function run()
	{
		\DB::table('cytrus_users')->truncate();

		$group1 = \Sentry::findGroupById(1);
		$group2 = \Sentry::findGroupById(2);
		$group3 = \Sentry::findGroupById(3);

		$user1 = \Sentry::createUser(array(
			'login' => 'magus',
			'password' => 'test',
			'email' => 'denis-shumeev@yandex.ru',
			'first_name' => 'Denis',
			'last_name' => 'Shumeev',
			'parameters' => null,
			'permissions' => array('superuser' => 1,),
			'activated' => true,
		));

		$user2 = \Sentry::createUser(array(
			'login' => 'admin',
			'password' => 'pass',
			'email' => 'info@lemon-tree.ru',
			'first_name' => 'Jeremy',
			'last_name' => 'Fisher',
			'parameters' => null,
			'permissions' => array(),
			'activated' => true,
		));

		$user2->addGroup($group1);

	}

}
