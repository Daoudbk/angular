<?php namespace LemonTree;

use Illuminate\Database\Seeder;

class GroupTableSeeder extends Seeder {

	public function run()
	{
		\DB::table('cytrus_users_groups')->truncate();

		\DB::table('cytrus_groups')->truncate();

		\Sentry::createGroup(array(
			'name' => 'Системные пользователи',
			'default_permission' => 'delete',
			'permissions' => array(
				'admin' => 1,
			),
		));

		\Sentry::createGroup(array(
			'name' => 'Администраторы',
			'default_permission' => 'delete',
			'permissions' => array(
				'admin' => 0,
			),
		));

		\Sentry::createGroup(array(
			'name' => 'Модераторы',
			'default_permission' => 'deny',
			'permissions' => array(
				'admin' => 0,
			),
		));

	}

}
