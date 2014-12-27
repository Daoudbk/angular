<?php namespace LemonTree;

final class Element extends \Eloquent {

	const ID_SEPARATOR = '.';

	public static function getByClassId($classId)
	{
		if ( ! strpos($classId, self::ID_SEPARATOR)) return null;

		try {

			list($class, $id) = explode(self::ID_SEPARATOR, $classId);

			return $class::find($id);

		} catch (\Exception $e) {}

		return null;
	}

	public static function getWithTrashedByClassId($classId)
	{
		if ( ! strpos($classId, self::ID_SEPARATOR)) return null;

		try {

			list($class, $id) = explode(self::ID_SEPARATOR, $classId);

			return
				$class::withTrashed()->
				cacheTags($class)->
				rememberForever()->
				find($id);

		} catch (\Exception $e) {}

		return null;
	}

	public static function getOnlyTrashedByClassId($classId)
	{
		if ( ! strpos($classId, self::ID_SEPARATOR)) return null;

		try {

			list($class, $id) = explode(self::ID_SEPARATOR, $classId);

			return
				$class::onlyTrashed()->
				cacheTags($class)->
				rememberForever()->
				find($id);

		} catch (\Exception $e) {}

		return null;
	}

}