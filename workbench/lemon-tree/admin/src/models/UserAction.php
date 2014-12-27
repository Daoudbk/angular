<?php namespace LemonTree;

class UserAction extends \Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cytrus_user_actions';

	public static function boot()
	{
		parent::boot();

		static::created(function($element) {
			$element->flush();
		});

		static::saved(function($element) {
			$element->flush();
		});

		static::deleted(function($element) {
			$element->flush();
		});
    }

	public function flush()
	{
		\Cache::tags('UserAction')->flush();
	}

	public function newQuery($excludeDeleted = true)
	{
		$builder = parent::newQuery();

		return $builder->cacheTags('UserAction')->rememberForever();
	}

	public function user()
	{
		return $this->belongsTo('LemonTree\User');
	}

	public function getActionTypeName()
	{
		return UserActionType::getActionTypeName($this->action_type);
	}

	public static function log($actionType, $comments)
	{
		$loggedUser = LoggedUser::getUser();

		$method =
			isset($_SERVER['REQUEST_METHOD'])
			? strtolower($_SERVER['REQUEST_METHOD'])
			: 'get';

		if($method == 'post') {

			$referer =
				isset($_SERVER["HTTP_REFERER"])
				? $_SERVER['HTTP_REFERER']
				: '';

			$url = $referer;

		} else {

			$server =
				isset($_SERVER['HTTP_HOST'])
				? $_SERVER['HTTP_HOST']
				: (defined('HTTP_HOST') ? HTTP_HOST : '');

			$uri =
				isset($_SERVER['REQUEST_URI'])
				? $_SERVER['REQUEST_URI']
				: '';

			$url = 'http://'.$server.$uri;

		}

		$userAction = new UserAction;

		$userAction->user_id = $loggedUser->id;
		$userAction->action_type = $actionType;
		$userAction->comments = $comments;
		$userAction->url = $url;

		$userAction->save();
	}

}
