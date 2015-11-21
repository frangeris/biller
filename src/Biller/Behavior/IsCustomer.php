<?php

namespace Biller\Behavior;

use Biller\Entity\Customer;

trait IsCustomer
{
    /**
     * Default var to use as user id.
     *
     * @var string
     */
    private $_id = 'id';

    /**
     * Default var to use as user id.
     *
     * @var string
     */
    private $_email = 'email';

    /**
     * Create a stripe customer using data of user.
     *
     * @package Customers
     * @param string $token    Token returned by stripe
     * @param array  $metadata Stripe metadata
     *
     * @return Stripe\Customer Object customer from stripe
     */
    public function toCustomer($token, $metadata = [])
    {
        $config = \Phalcon\DI::getDefault()->getConfig();

        // custom user id
        if (isset($config->biller->custom_id)) {
            $this->_id = $config->biller->custom_id;
        }

        // custom email
        if (isset($config->biller->custom_email)) {
            $this->_email = $config->biller->custom_email;
        }

        // required user id field customer table
        if (!isset($this->{$this->_id})) {
            throw new \Exception('User must have an id to make the relationship to customers table');
        }

        // required email field for stripe
        if (!isset($this->{$this->_email})) {
            throw new \Exception('User must have an email');
        }

        // customer data
        $args = ['source' => $token, 'email' => $this->email];
        foreach ($metadata as $property) {
            if (isset($this->$property)) {
                $args['metadata'][$property] = $this->$property;
            }
        }

        $stripe_customer = \Stripe\Customer::create($args);

        // save the customer for future charges
        $customer = new Customer();
        $customer->user_id = $this->{$this->_id};
        $customer->stripe_id = $stripe_customer->id;
        $customer->token = $token;
        $customer->save();

        return $stripe_customer;
    }

    /**
     * Get the customer with stripe attributes.
     *
     * @package Customers
     * @param string $attribute Attribute of the customer (stripe)
     *
     * @return Biller\Entity\Customer Customer object
     */
    public function customer($attribute = null)
    {
        $customer = Customer::findFirst(["user_id = '{$this->id}'"]);
        $customer_s = \Stripe\Customer::retrieve($customer->stripe_id);

        // merge stripe customer attibutes into entity
        foreach ($customer_s->keys() as $key) {
            $customer->$key = $customer_s->$key;
        }

        if (!is_null($attribute)) {
            return $customer->$attribute;
        }

        return $customer;
    }
}
