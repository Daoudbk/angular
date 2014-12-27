<?php namespace LemonTree;

class Group extends \Cartalyst\Sentry\Groups\Eloquent\Group {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cytrus_groups';
	protected $pivotTable = 'cytrus_users_groups';

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
		\Cache::tags('Group')->flush();
	}

	public function newQuery($excludeDeleted = true)
	{
		$builder = parent::newQuery();

		return $builder->cacheTags('Group', 'UserGroup')->rememberForever();
	}

	public function users()
	{
		return $this->belongsToMany('LemonTree\User', $this->pivotTable);
	}

	public function itemPermissions()
	{
		return $this->hasMany('LemonTree\GroupItemPermission')->
			cacheTags('GroupItemPermission.'.$this->id)->
			rememberForever();
	}

	public function elementPermissions()
	{
		return $this->hasMany('LemonTree\GroupElementPermission')->
			cacheTags('GroupElementPermission.'.$this->id)->
			rememberForever();
	}

	public function getItemPermission($class)
	{
		return $this->itemPermissions()->where('class', $class)->first();
	}

	public function getElementPermission($classId)
	{
		return $this->elementPermissions()->where('class_id', $classId)->first();
	}

	public function getItemAccess(Item $item)
	{
		$itemPermission = $this->getItemPermission($item->getName());

		if ($itemPermission) return $itemPermission->permission;

		return $this->default_permission;
	}

	public function getElementAccess(ElementInterface $element)
	{
		$elementPermission = $this->getElementPermission($element->getClassId());

		if ($elementPermission) return $elementPermission->permission;

		$itemPermission = $this->getItemPermission($element->getClass());

		if ($itemPermission) return $itemPermission->permission;

		return $this->default_permission;
	}

}
