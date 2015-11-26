<?php

namespace Tests;

require_once(__DIR__.'/../vendor/autoload.php');

/**
 * Mock class for test
 */
class User extends \Phalcon\Mvc\Model
{
	use \Biller\Behavior\IsCustomer;

	public $id = 1;
	public $email = 'test@example.com';
}