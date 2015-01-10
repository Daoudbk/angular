<?php namespace LemonTree;

class PluginController extends \BaseController {

	public function getBrowsePlugin($classId)
	{
		$scope = array();

		$site = \App::make('site');

		$browsePlugin = $site->getBrowsePlugin($classId);

		$scope['plugin'] = $browsePlugin;

		return \Response::json($scope);
	}

}
