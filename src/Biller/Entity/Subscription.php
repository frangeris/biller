<?php

namespace Biller\Entity;

use Phalcon\Mvc\Model;

class Subscription extends Model
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
     * Initialize the model.
     */
    public function initialize()
    {
        $this->setSource('subscriptions');
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
