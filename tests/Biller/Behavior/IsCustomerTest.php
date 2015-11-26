<?php

namespace Tests\Biller\Behavior;

use Tests\TestCase;

class IsCustomerTest extends TestCase
{
    public function testUserToCustomerConvertion()
    {
        $mock = $this->getMockForTrait('Biller\Behavior\IsCustomer');
        $mock->id = 1;
        $mock->email = 'test@example.com';

        $token = \Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 5,
                'exp_year' => date('Y') + 3,
                'cvc' => '314',
            ],
        ]);

        $this->assertInstanceOf('Stripe\Customer', static::$mock->toCustomer($token->id));
    }

    public function testGetuserObjectFromStripeOrSession()
    {
        $this->assertInstanceOf('Stripe\Customer', static::$mock->customer());
    }

    public function testGetFieldFromStripeCustomer()
    {
        $this->assertEquals('customer', static::$mock->customer('object'));
    }

    public function testGetSubscriptionOfCustomer()
    {
        $this->assertInstanceOf('Biller\Handler\Subscription', static::$mock->subscription());
    }

    public function testSetTrialPeriodToCustomer()
    {
        $days = 14;
        static::$mock->trial($days);

        $this->assertEquals(static::$mock->trial_end, \Carbon\Carbon::now()->addDays($days)->timestamp);
    }

    public function testVerifySubscribedCustomerStatus()
    {
        $this->assertInternalType('bool', static::$mock->subscribed());
    }

    public function testVerifyCanceledSubscriptionStatus()
    {
        $this->assertInternalType('bool', static::$mock->canceled());
    }

    public function testCustomerStatusInAPlan()
    {
        $this->assertInternalType('bool', static::$mock->onPlan('basic'));
    }

    public function testVerifyOnTrialSubscriptionStatus()
    {
        $this->assertInternalType('bool', static::$mock->onTrial());
    }
}
