<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class SiteUser extends Eloquent implements LemonTree\ElementInterface, UserInterface, RemindableInterface {

	use LemonTree\ElementTrait, UserTrait, RemindableTrait;

	protected $hidden = array('password', 'remember_token');

}
