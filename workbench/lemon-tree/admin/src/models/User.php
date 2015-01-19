<?php namespace LemonTree;

class User extends \Cartalyst\Sentry\Users\Eloquent\User {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cytrus_users';

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
		\Cache::tags('User', 'Group')->flush();
	}

	public function newQuery($excludeDeleted = true)
	{
		$builder = parent::newQuery();

		return $builder->cacheTags('User', 'UserGroup')->rememberForever();
	}

	public function getUnserializedParameters()
	{
		try {
			return unserialize($this->parameters);
		} catch (\Exception $e) {}

		return null;
	}

	public function parameterExists($name)
	{
		$unserializedParameters = $this->getUnserializedParameters();

		return isset($unserializedParameters[$name]);
	}

	public function getParameter($name)
	{
		$unserializedParameters = $this->getUnserializedParameters();

		return
			isset($unserializedParameters[$name])
			? $unserializedParameters[$name]
			: null;
	}

	public function setParameter($name, $value)
	{
		try {

			$unserializedParameters = $this->getUnserializedParameters();

			$unserializedParameters[$name] = $value;

			$parameters = serialize($unserializedParameters);

			$this->parameters = $parameters;

			$this->save();
			
		} catch (\Exception $e) {}

		return $this;
	}

	public function tabs()
	{
		return $this->hasMany('LemonTree\Tab')->orderBy('id');
	}

	public function hasUpdateDefaultAccess(Item $item)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			$access = $group->getItemAccess($item);
			if (in_array($access, array('update', 'delete'))) {
				return true;
			}
		}

		return false;
	}

	public function hasViewAccess(ElementInterface $element)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			$access = $group->getElementAccess($element);
			if (in_array($access, array('view', 'update', 'delete'))) {
				return true;
			}
		}

		return false;
	}

	public function hasUpdateAccess(ElementInterface $element)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			$access = $group->getElementAccess($element);
			if (in_array($access, array('update', 'delete'))) {
				return true;
			}
		}

		return false;
	}

	public function hasDeleteAccess(ElementInterface $element)
	{
		if ($this->isSuperUser()) return true;

		$groups = $this->getGroups();

		foreach ($groups as $group) {
			$access = $group->getElementAccess($element);
			if (in_array($access, array('delete'))) {
				return true;
			}
		}

		return false;
	}

}
