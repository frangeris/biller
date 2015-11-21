<?php

namespace Biller\Entity;

class Customer extends \Phalcon\Mvc\Model
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var int
	 */
	public $user_id;

	/**
	 * @var string
	 */
	public $stripe_id;

	/**
	 * @var string
	 */
	public $token;

	/**
	 * @var string
	 */
	public $created_at;

	/**
	 * @var string
	 */
	public $updated_at;

    /**
     * Initialize the model
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('customers');
    }

    /**
     * Before create
     *
     * @return void
     */
    public function beforeCreate()
    {
        $this->created_at = date('Y-m-d H:i:s');
    }
}
