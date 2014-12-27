<?php namespace LemonTree\Admin;

use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('lemon-tree/admin');

		$site = \App::make('site');

		$site->initMicroTime();

		if (file_exists($path = app_path().'/site.php')) {
			include $path;
		}

		if (file_exists(
			$swift_init = app_path()
				.'/../vendor/swiftmailer/swiftmailer/lib/swift_init.php'
		)) {
			require_once $swift_init;
		}

		\App::error(function(\Exception $exception, $code) {
			\Log::error($exception);
			\LemonTree\ErrorMessageUtils::sendMessage($exception);
			if (\Config::get('app.debug') !== true) {
				return \Response::view('error500', array(), 500);
			}
		});

		\App::missing(function($exception) {
			return \Response::view('error404', array(), 404);
		});

		\Cache::extend('file', function($app) {
			return new \Illuminate\Cache\Repository(
				new \LemonTree\CustomFileStore($app['files'], $app['config']['cache.path'])
			);
		});

		\Blade::extend(function($value) {
			return preg_replace('/\{\?(.+)\?\}/', '<?php ${1} ?>', $value);
		});

		\Config::set(
			'cartalyst/sentry::groups.model',
			'LemonTree\Group');

		\Config::set(
			'cartalyst/sentry::users.model',
			'LemonTree\User');

		\Config::set(
			'cartalyst/sentry::users.login_attribute',
			'login');

		\Config::set(
			'cartalyst/sentry::user_groups_pivot_table',
			'cytrus_users_groups');

		\Config::set(
			'cartalyst/sentry::throttling.model',
			'LemonTree\Throttle');

		include __DIR__.'/../../routes.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		\App::singleton('site', function($app) {
			return new \LemonTree\Site;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
