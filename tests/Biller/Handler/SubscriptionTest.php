<?php

namespace Tests\Biller\Handler;

use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    public function testCustomerCanAccessToASubcription()
    {
        $this->assertInternalType('bool', static::$mock->subscribed());
    }

    public function testCreateSubscriptionToCustomer()
    {
        $this->assertInstanceOf('Stripe\Subscription', static::$mock->subscription()->go('basic'));
    }

    public function testChangeSubscriptionToCustomer()
    {
        $this->assertInstanceOf('Stripe\Subscription', static::$mock->subscription()->go('premium'));
    }

    public function testChangeSubscriptionWithTrial()
    {
        $this->assertInstanceOf('Stripe\Subscription', static::$mock->subscription()->trial(14)->go('basic'));
    }

    public function testChangeSubscriptionWithCoupon()
    {
        $this->assertInstanceOf('Stripe\Subscription', static::$mock->subscription()->apply(self::coupon('25off'))->go('basic'));
    }

    /**
     * @depends testChangeSubscriptionWithCoupon
     */
    public function testCustomerCanCancelASubscription()
    {
        $this->assertInstanceOf('Stripe\Subscription', static::$mock->subscription()->cancel(true));
    }

    public function testSetTrialPeriodToCustomerOnSubscription()
    {
        $this->assertInstanceOf('Biller\Handler\Subscription', static::$mock->subscription()->trial(14));
    }

    public function testSubscriptionCanApplyCoupon()
    {
        $this->assertInstanceOf('Biller\Handler\Subscription', static::$mock->subscription()->apply(self::coupon('25off')));
    }
}