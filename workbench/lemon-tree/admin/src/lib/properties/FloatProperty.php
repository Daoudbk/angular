<?php namespace LemonTree;

class FloatProperty extends BaseProperty {

	public function __construct($name) {
		parent::__construct($name);

		$this->
		addRule('integer', 'Введите число с запятой');

		return $this;
	}

	public static function create($name)
	{
		return new self($name);
	}

	public function searchQuery($query)
	{
		$name = $this->getName();

		$from = \Input::get($name.'_from');
		$to = \Input::get($name.'_to');

		if (strlen($from)) {
			$from = str_replace(array(',', ' '), array('.', ''), $from);
			$query->where($name, '>=', (double)$from);
		}

		if (strlen($to)) {
			$to = str_replace(array(',', ' '), array('.', ''), $to);
			$query->where($name, '<=', (double)$to);
		}

		return $query;
	}

	public function searching()
	{
		$name = $this->getName();

		$from = \Input::get($name.'_from');
		$to = \Input::get($name.'_to');

		return strlen($from) || strlen($to)
			? true : false;
	}

	public function getBrowseEditView()
	{
		$scope = array(
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'element' => $this->getElement(),
			'readonly' => $this->getReadonly(),
		);

		try {
			$view = $this->getClassName().'.browseEdit';
			return \View::make('admin::properties.'.$view, $scope);
		} catch (\Exception $e) {}

		return null;
	}

	public function getElementSearchView()
	{
		$scope = array(
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'from' => \Input::get($this->getName().'_from'),
			'to' => \Input::get($this->getName().'_to'),
		);

		try {
			$view = $this->getClassName().'.elementSearch';
			return \View::make('admin::properties.'.$view, $scope);
		} catch (\Exception $e) {}

		return null;
	}

}
