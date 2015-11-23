<?php

namespace Biller\Behavior;

use Biller\Entity\Customer;
use Biller\Entity\Subscription;
use Biller\Handler\Subscription as Handler;

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
     * @param string $token    Token returned by stripe
     * @param array  $metadata Stripe metadata
     *
     * @return Stripe\Customer Object customer from stripe
     */
    public function toCustomer($token, $plan = null, $metadata = [])
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

        // customer paramters
        $args = ['source' => $token, 'email' => $this->email];

        // trial
        if (isset($this->trial_ends)) {
            $args['trial_end'] = $this->trial_end;
        }

        // customer metadata
        foreach ($metadata as $property) {
            if (isset($this->$property)) {
                $args['metadata'][$property] = $this->$property;
            }
        }

        $stripe_customer = \Stripe\Customer::create($args);

        // save new customer
        $customer = new Customer();
        $customer->user_id = $this->{$this->_id};
        $customer->stripe_id = $stripe_customer->id;
        $customer->token = $token;
        $customer->save();
        // $customer->merge($stripe_customer);

        // save the customer for future charges
        $session = \Phalcon\DI::getDefault()->getSession();
        $session->set('biller.customer', $stripe_customer);

        return $stripe_customer;
    }

    /**
     * Get the customer with stripe attributes.
     *
     * @param string $attribute Attribute of the customer (stripe)
     *
     * @return Biller\Entity\Customer Customer object
     */
    public function customer($attribute = null)
    {
        $session = \Phalcon\DI::getDefault()->getSession();
        if ($session->has('biller.customer')) {
            return $session->get('biller.customer');
        }

        $customer = Customer::findFirst(["user_id = '{$this->id}'"]);
        $stripe_customer = \Stripe\Customer::retrieve($customer->stripe_id);
        // $customer->merge($stripe_customer);

        // specific attibute
        if (!is_null($attribute) && isset($stripe_customer->$attribute)) {
            return $stripe_customer->$attribute;
        } elseif (!is_null($attribute)) {
            throw new \Exception(sprintf('Accesing to undefined property "%s" of customer', $attribute));
        }

        // cache
        $session->set('biller.customer', $stripe_customer);

        return $stripe_customer;
    }

    /**
     * Interact with subcription handler.
     *
     * @return Biller\Handler\Subscription Instance of handler
     */
    public function subscription()
    {
        $session = \Phalcon\DI::getDefault()->getSession();
        if ($session->has('biller.subs')) {
            $subscription = $session->get('biller.subs');
        } else {
            // get the current subscription
            $subscription = Subscription::findFirst(["user_id = '{$this->{$this->_id}}'"]);
        }

        return Handler::instance($this, $subscription);
    }

    /**
     * Set days to trial in customer creation.
     *
     * @param int $days Days of trial period
     *
     * @return Biller\Behavior\IsCustomer
     */
    public function trial($days = 14)
    {
        $this->trial_end = \Carbon\Carbon::now()->addDays($days)->timestamp;

        return $this;
    }

    /**
     * Verify is the user is subscribed
     *
     * @return bool Boolean status of subscription
     */
    public function subscribed()
    {
        return !in_array($this->subscription()->current->status, ['canceled', 'unpaid']);
    }

    /**
     * Verify is the user has canceled the subscription
     *
     * @return bool Boolean status of subscription
     */
    public function canceled()
    {
        return 'canceled' == $this->subscription()->current->status;
    }

    /**
     * Verify is the user is on the plan
     *
     * @return bool Boolean status of plan
     */
    public function onPlan($plan)
    {
        return $plan == $this->subscription()->current->plan;
    }

    /**
     * Verify is the user is on trial period
     *
     * @return bool Boolean status of plan
     */
    public function onTrial()
    {
        return 'trialing' == $this->subscription()->current->status;
    }
}
