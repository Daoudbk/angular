<?php namespace LemonTree;

$site = \App::make('site');

$site->

	/*
	 * Пользователь
	 */

	addItem(
		Item::create('SiteUser')->
		setTitle('Пользователь')->
		setMainProperty('email')->
		addOrderBy('created_at', 'desc')->
		addProperty(
			TextfieldProperty::create('email')->
			setTitle('Email')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('fio')->
			setTitle('Имя, фамилия')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('phone')->
			setTitle('Телефон')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('phone2')->
			setTitle('Телефон 2')->
			setShow(true)
		)->
		addProperty(
			FloatProperty::create('discount')->
			setTitle('Скидка, %')
		)->
		addProperty(
			CheckboxProperty::create('activated')->
			setTitle('Активирован')
		)->
		addProperty(
			CheckboxProperty::create('banned')->
			setTitle('Забанен')
		)->
		addProperty(
			TextareaProperty::create('comments')->
			setTitle('Комментарий менеджера')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Категория товаров
	 */

	addItem(
		Item::create('Category')->
		setTitle('Категория товаров')->
		setMainProperty('name')->
		setRoot(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			ImageProperty::create('image')->
			setTitle('Изображение')->
			setResize(205, 139, 80)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('url')->
			setTitle('URL')->
			setRequired(true)->
			addRule('regex:/^[a-z0-9\-]+$/i', 'Допускаются латинские буквы, цифры и дефис')
		)->
		addProperty(
			TextfieldProperty::create('title')->
			setTitle('Title')
		)->
		addProperty(
			TextareaProperty::create('shortcontent')->
			setTitle('Краткое описание')->
			setShow(true)->
			setEditable(true)
		)->
		addProperty(
			RichtextProperty::create('fullcontent')->
			setTitle('Полное описание')
		)->
		addProperty(
			CheckboxProperty::create('hide')->
			setTitle('Скрыть')->
			setShow(true)->
			setEditable(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Подкатегория товаров
	 */

	addItem(
		Item::create('Subcategory')->
		setTitle('Подкатегория товаров')->
		setMainProperty('name')->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('url')->
			setTitle('URL')->
			setRequired(true)->
			addRule('regex:/^[a-z0-9\-]+$/i', 'Допускаются латинские буквы, цифры и дефис')
		)->
		addProperty(
			TextfieldProperty::create('title')->
			setTitle('Title')
		)->
		addProperty(
			RichtextProperty::create('fullcontent')->
			setTitle('Полное описание')
		)->
		addProperty(
			CheckboxProperty::create('hide')->
			setTitle('Скрыть')->
			setShow(true)->
			setEditable(true)
		)->
		addProperty(
			OneToOneProperty::create('category_id')->
			setTitle('Категория товаров')->
			setRelatedClass('Category')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setParent(true)->
			setRequired(true)->
			bind('Category')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Бренд товара
	 */

	addItem(
		Item::create('GoodBrand')->
		setTitle('Бренд товара')->
		setMainProperty('name')->
		addOrderBy('name', 'asc')->
		addOrderBy('created_at', 'desc')->
		setPerPage(3)->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('title')->
			setTitle('Title')
		)->
		addProperty(
			TextfieldProperty::create('h1')->
			setTitle('H1')
		)->
		addProperty(
			RichtextProperty::create('fullcontent')->
			setTitle('Полное описание')
		)->
		addProperty(
			TextfieldProperty::create('address')->
			setTitle('Адрес поставщика')
		)->
		addProperty(
			CheckboxProperty::create('hide')->
			setTitle('Скрыть')->
			setShow(true)
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('ServiceSection')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setReadonly(true)->
			setParent(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Товар
	 */

	addItem(
		Item::create('Good')->
		setTitle('Товар')->
		setMainProperty('name')->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('url')->
			setTitle('URL')->
			setRequired(true)->
			addRule('regex:/^[a-z0-9\-]+$/i', 'Допускаются латинские буквы, цифры и дефис')
		)->
		addProperty(
			TextfieldProperty::create('code')->
			setTitle('Артикул')->
			setRequired(true)->
			setShow(true)->
			setEditable(true)
		)->
		addProperty(
			ImageProperty::create('image')->
			setTitle('Изображение')->
			setResize(300, 350, 80)->
			addResize('spec', 150, 200, 80)->
			addResize('other', 100, 100, 80)
		)->
		addProperty(
			FloatProperty::create('supplier_price')->
			setTitle('Цена поставщика')->
			setRequired(true)->
			setShow(true)->
			setEditable(true)
		)->
		addProperty(
			FloatProperty::create('price')->
			setTitle('Цена')->
			setRequired(true)->
			setShow(true)->
			setEditable(true)
		)->
		addProperty(
			FloatProperty::create('price2')->
			setTitle('Цена 2')
		)->
		addProperty(
			FloatProperty::create('price3')->
			setTitle('Цена 3')
		)->
		addProperty(
			TextfieldProperty::create('title')->
			setTitle('Title')
		)->
		addProperty(
			TextfieldProperty::create('meta_keywords')->
			setTitle('META Keywords')
		)->
		addProperty(
			TextareaProperty::create('meta_description')->
			setTitle('META Description')
		)->
		addProperty(
			TextareaProperty::create('shortcontent')->
			setTitle('Краткое описание')
		)->
		addProperty(
			RichtextProperty::create('fullcontent')->
			setTitle('Полное описание')
		)->
		addProperty(
			CheckboxProperty::create('special')->
			setTitle('Спецпредложение')
		)->
		addProperty(
			CheckboxProperty::create('novelty')->
			setTitle('Новинка')
		)->
		addProperty(
			CheckboxProperty::create('hide')->
			setTitle('Скрыть')->
			setShow(true)->
			setEditable(true)
		)->
		addProperty(
			CheckboxProperty::create('absent')->
			setTitle('Нет в наличии')->
			setShow(true)->
			setEditable(true)
		)->
		addProperty(
			OneToOneProperty::create('category_id')->
			setTitle('Категория товара')->
			setRelatedClass('Category')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setRequired(true)->
			setParent(true)->
			bind('Category')
		)->
		addProperty(
			OneToOneProperty::create('subcategory_id')->
			setTitle('Подкатегория товара')->
			setRelatedClass('Subcategory')->
			setDeleting(OneToOneProperty::RESTRICT)->
			bind('Category')->
			bind('Category', 'Subcategory')
		)->
		addProperty(
			OneToOneProperty::create('good_brand_id')->
			setTitle('Бренд товара')->
			setRelatedClass('GoodBrand')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setRequired(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Раздел сайта
	 */

	addItem(
		Item::create('Section')->
		setTitle('Раздел сайта')->
		setMainProperty('name')->
		setRoot(true)->
		setElementPermissions(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('url')->
			setTitle('Адрес страницы')->
			addRule('regex:/^[a-z0-9\-]+$/i', 'Допускаются латинские буквы, цифры и дефис')
		)->
		addProperty(
			TextfieldProperty::create('title')->
			setTitle('Title')
		)->
		addProperty(
			TextfieldProperty::create('h1')->
			setTitle('H1')
		)->
		addProperty(
			TextfieldProperty::create('meta_keywords')->
			setTitle('META Keywords')
		)->
		addProperty(
			TextareaProperty::create('meta_description')->
			setTitle('META Description')
		)->
		addProperty(
			TextareaProperty::create('shortcontent')->
			setTitle('Краткий текст')
		)->
		addProperty(
			RichtextProperty::create('fullcontent')->
			setTitle('Текст раздела')
		)->
		addProperty(
			OneToOneProperty::create('section_id')->
			setTitle('Раздел сайта')->
			setRelatedClass('Section')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setParent(true)->
			bind(Site::ROOT, 'Section')->
			bind('Section', 'Section')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Служебный раздел
	 */

	addItem(
		Item::create('ServiceSection')->
		setTitle('Служебный раздел')->
		setMainProperty('name')->
		setRoot(true)->
		setElementPermissions(true)->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('ServiceSection')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setParent(true)->
			bind(Site::ROOT, 'ServiceSection')->
			bind('ServiceSection', 'ServiceSection')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Настройки сайта
	 */

	addItem(
		Item::create('SiteSettings')->
		setTitle('Настройки сайта')->
		setMainProperty('name')->
		setRoot(true)->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextfieldProperty::create('title')->
			setTitle('Title')->
			setRequired(true)
		)->
		addProperty(
			TextfieldProperty::create('h1')->
			setTitle('H1')
		)->
		addProperty(
			TextareaProperty::create('description')->
			setTitle('META Description')
		)->
		addProperty(
			TextfieldProperty::create('keywords')->
			setTitle('META Keywords')
		)->
		addProperty(
			RichtextProperty::create('text')->
			setTitle('Текст')
		)->
		addProperty(
			TextfieldProperty::create('copyright')->
			setTitle('Copyright')
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Счетчик
	 */

	addItem(
		Item::create('Counter')->
		setTitle('Счетчик')->
		setMainProperty('name')->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextareaProperty::create('code')->
			setTitle('Код счетчика')
		)->
		addProperty(
			TextareaProperty::create('logo')->
			setTitle('Логотип счетчика')
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('ServiceSection')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setReadonly(true)->
			setParent(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Категория расходов
	 */

	addItem(
		Item::create('ExpenseCategory')->
		setTitle('Категория расходов')->
		setMainProperty('name')->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('ServiceSection')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setReadonly(true)->
			setParent(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Источник расходов
	 */

	addItem(
		Item::create('ExpenseSource')->
		setTitle('Источник расходов')->
		setMainProperty('name')->
		addOrder()->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('ServiceSection')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setReadonly(true)->
			setParent(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	/*
	 * Расход
	 */

	addItem(
		Item::create('Expense')->
		setTitle('Расход')->
		setMainProperty('name')->
		addOrderBy('name', 'asc')->
		addProperty(
			TextfieldProperty::create('name')->
			setTitle('Название')->
			setRequired(true)->
			setShow(true)
		)->
		addProperty(
			TextareaProperty::create('comment')->
			setTitle('Примечание')
		)->
		addProperty(
			OneToOneProperty::create('service_section_id')->
			setTitle('Служебный раздел')->
			setRelatedClass('ServiceSection')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setReadonly(true)->
			setParent(true)
		)->
		addProperty(
			OneToOneProperty::create('expense_category_id')->
			setTitle('Категория расходов')->
			setRelatedClass('ExpenseCategory')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setRequired(true)
		)->
		addProperty(
			OneToOneProperty::create('expense_source_id')->
			setTitle('Источник расходов')->
			setRelatedClass('ExpenseSource')->
			setDeleting(OneToOneProperty::RESTRICT)->
			setRequired(true)
		)->
		addTimestamps()->
		addSoftDeletes()
	)->

	bind(Site::ROOT, 'Category', 'Section', 'ServiceSection')->
	bind('Category', 'Subcategory', 'Good')->
	bind('Subcategory', 'Good')->
	bind('ServiceSection.1', 'ServiceSection')->
	bind('ServiceSection.4', 'Counter')->
	bind('ServiceSection.6', 'ServiceSection')->
	bind('ServiceSection.7', 'ServiceSection')->
	bind('ServiceSection.12', 'ExpenseCategory')->
	bind('ServiceSection.13', 'ExpenseSource')->
	bind('ServiceSection.14', 'GoodBrand')->

	bindTree(Site::ROOT, 'Category', 'Section', 'ServiceSection', 'SiteSettings')->
	bindTree('Category', 'Subcategory', 'Good')->
	bindTree('Subcategory', 'Good')->
	bindTree('Section', 'Section')->
	bindTree('ServiceSection.1', 'ServiceSection')->
	bindTree('ServiceSection.4', 'Counter')->
	bindTree('ServiceSection.6', 'ServiceSection')->
	bindTree('ServiceSection.7', 'ServiceSection')->
	bindTree('ServiceSection.12', 'ExpenseCategory')->
	bindTree('ServiceSection.13', 'ExpenseSource')->
	bindTree('ServiceSection.14', 'GoodBrand')->

	bindBrowsePlugin('ServiceSection.8', 'MoneyStat')->
	bindSearchPlugin('Good', 'GoodSearch')->
	bindEditPlugin('ServiceSection', 'MoneyStat2')->
	bindBrowseFilter('Good', 'GoodFilter')->

	end();
