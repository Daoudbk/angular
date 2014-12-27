<?php namespace LemonTree;

class Throttle extends \Cartalyst\Sentry\Throttling\Eloquent\Throttle {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cytrus_throttle';

	public function newQuery($excludeDeleted = true)
	{
		$builder = parent::newQuery();

		return $builder->cacheTags('Throttle')->rememberForever();
	}

}
