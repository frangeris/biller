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
            $this->subs = $subscription;
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

        if (isset($this->subs)) {
            // user already have a previous subscription
            $stripe_subs = $customer->subscriptions->retrieve($this->subs->stripe_id);
            $stripe_subs->plan = $plan;
            $stripe_subs = $stripe_subs->save();

            $this->subs->plan = $plan;
        } else {
            // create the subscription
            $stripe_subs = $customer->subscriptions->create([
                'plan' => $plan,
            ]);

            // save the subscription on db
            $this->subs = new Entity();
            $this->subs->user_id = $this->user->id;
            $this->subs->stripe_id = $stripe_subs->id;
            $this->subs->plan = $plan;
        }

        // update locally
        $this->subs->save();

        // cache load
        $this->session->set('biller.sub', $stripe_subs);

        return $stripe_subs;
    }

    /**
     * Cancel a subcription
     *
     * @param  bool|boolean $ends_at Delay the cancellation of the subscription until the end of the current period.
     * @return Stripe\Subscription Stripe canceled subscription object
     */
    public function cancel(boolean $ends_at = false)
    {
    	$customer = $this->user->customer();

    	return $customer->subscriptions->retrieve($this->subs->stripe_id)->cancel($ends_at);
    }

    public function trial()
    {
    }

    public function ends($timestamp)
    {
        // tiempo en el que finaliza un plan
    }

    public function increase($quantity)
    {
    }

    public function decrease($quantity)
    {
    }

    public function withCoupon($code)
    {
    }

    public function withCard($choice)
    {
    }
}
