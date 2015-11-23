<?php

namespace Biller\Handler;

use Biller\Entity\Subscription as Entity;

class Subscription extends \Phalcon\Mvc\User\Component
{
    /**
     * @var Biller\Handler\Subscription
     */
    private static $instance;

    /**
     * Construct.
     */
    private function __construct(\Phalcon\Mvc\Model $user, $subscription)
    {
        $this->user = $user;

        if ($subscription instanceof \Phalcon\Mvc\Model) {
            $this->current = $subscription;
        }
    }

    /**
     * Get an intance.
     *
     * @return Biller\Handler\Subscription Instance
     */
    public static function instance($user, $subscription)
    {
        if (null == self::$instance) {
            self::$instance = new self($user, $subscription);
        }

        return self::$instance;
    }

    /**
     * Create or update the subscription of a customer.
     *
     * @param string $plan Id of the plan to use
     *
     * @return Stripe\Subscription Stripe subscription object
     */
    public function go($plan)
    {
        $customer = $this->user->customer();

        $args = [
        	'plan' => $plan,
        	'trial_end' => (isset($this->trial_end)) ? $this->trial_end : null,
        	'coupon' => (isset($this->coupon)) ? $this->coupon : null
        ];

        if (isset($this->current)) {
            // user already have a previous subscription
            $stripe_subs = $customer->subscriptions->retrieve($this->current->stripe_id);
            foreach ($args as $key => $value) {
            	$stripe_subs->$key = $value;
            }

            // update stripe and db
            $stripe_subs = $stripe_subs->save();
            $this->current->plan = $plan;
            $this->current->status = $stripe_subs->status;
        } else {
            // create the subscription
            $stripe_subs = $customer->subscriptions->create($args);

            // save the subscription on db
            $this->current = new Entity();
            $this->current->user_id = $this->user->id;
            $this->current->stripe_id = $stripe_subs->id;
            $this->current->status = $stripe_subs->status;
            $this->current->plan = $plan;
        }

        // update locally
        $this->current->save();

        // cache load
        $this->session->set('biller.sub', $this->current);

        return $stripe_subs;
    }

    /**
     * Cancel a subcription
     *
     * @param  bool|boolean $ends_at Delay the cancellation of the subscription until the end of the current period.
     * @return Stripe\Subscription Stripe canceled subscription object
     */
    public function cancel($ends_at = false)
    {
    	$customer = $this->user->customer();

    	return $customer->subscriptions->retrieve($this->current->stripe_id)->cancel($ends_at);
    }

    public function trial($days)
    {
        $this->trial_end = \Carbon\Carbon::now()->addDays($days)->timestamp;

        return $this;
    }

    public function withCoupon($code)
    {
        $this->coupon = $code;

        return $this;
    }

    public function increase($quantity = null)
    {
    }

    public function decrease($quantity = null)
    {
    }
}
