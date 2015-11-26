# Biller (for phalconphp)

## Introduction
Biller is a billing service package created for abstraction of [Subscriptions](hhttps://stripe.com/docs/api#subscriptions), [Charges](https://stripe.com/docs/api#intro) and [Customers](https://stripe.com/docs/api#customers) using [Stripe API](https://stripe.com/docs/api#intro) under [PhalconPHP Framework](https://phalconphp.com), inspired by [Laravel Cashier](https://github.com/laravel/cashier), so let's rock and roll...

## Installation
The recommended way to install Biller is through Composer.

#### Install Composer

```sh
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Biller:

```sh
composer.phar require frangeris/biller
```

## Getting Started
This vendor use connection to Mysql database for manage data of stripe. The first step is create those tables (Subscriptions, Customers), we'll use [Phalcon database migration](https://docs.phalconphp.com/en/latest/reference/migrations.html) for this, using [Phalcon Developer Tools](https://docs.phalconphp.com/en/latest/reference/tools.html).

#### Run migrations
```sh
$ phalcon migration --action=run --migrations=migrations --config=</path/to/config.php>
```

This simply add two more tables to your current database (will be used by the vendor to keep record of the data in stripe).


#### Initialize the gateway

The next step is to start with the implementation directly in code, before make any kind of request, we must start `\Biller\Gateway`, this will allow us make continues request to [Stripe API](https://stripe.com/docs/api#intro).

Before start the **Gateway** we need to add the configuration of `Biller` in the configuration array of the app, with the next structure:

#### Add biller configuration to config file:

```php
return new \Phalcon\Config([
    'database' => [
        'adapter' => 'Mysql',
        'host' => '127.0.0.1',
		// ...
    ],
    // ------------- ADD THIS -------------
    'biller' => [
    	'key' => '<YOUR STRIPE KEY>', // the stripe key
    	'custom_id' => 'id', // primary key of your user model, default 'id'
    	'custom_email' => 'email', // email field to use from user model for customers, default 'email'
    ]
]);
```

> The fields `custom_id` and `custom_email` are the names of properties inside `User` model that represent such values.

####Start the gateway using `Gateway::me()`:

The **Gateway** receive as parameter an object `\Phalcon\Config` so we spend the configuration of the application previously loaded; use method `me()` for indicate who we are:

```php
/*
 * Read the configuration file
 */
$config = include __DIR__.'/../app/config/config.php';

// Initialize the gateway with the config var
\Biller\Gateway::me($config);
```

Done this, our app is connected with stripe and we can begin to treat `Users` as `Customers`, let's start:

#### Add a trait to User model:

To give the behavior of a `Customer` to the `User` model that we're using, simply add the trait  `Biller\Behavior\IsCustomer` to the `User` class that extends of `\Phalcon\Mvc\Model`.


```php
class User extends \Phalcon\Mvc\Model
{
    use Biller\Behavior\IsCustomer;

    // ...
}
```

Now, we can interact with our `User` as a `Customer` in stripe.

## Methods:

```php
// Get an user
$user = User::findFirst();

// create a new customer with pro plan using object attributes as metadata in stripe
$customer = $user->toCustomer($this->request->getPost('stripeToken'), 'pro', ['name', 'age', 'phone']);

// get customer stripe object
$user->customer('default_source');

// start a pro subscription with 14 days of trial
$user->subscription()->trial(14)->go('pro');

// get date when trial ends
$user->trial(14)->trial_end;

// go pro plan without trial
$user->subscription()->go('pro');

// change to enterprise plan
$user->subscription()->go('enterprise');

// go pro using a coupon
$user->subscription()->withCoupon('coupon')->go('pro');

// cancel the current subscription
$user->subscription()->cancel();
```

**Others methods to verify status**

```php
// verify if the user is subscribed
$user->subscribed();

// verify if the user has cancelled a subscription
$user->cancelled();

// verify if the user subscription is pro plan
$user->onPlan('pro');

// verify if the user is on a trial period
$user->onTrial();

```

## Development

Install dependencies:

``` bash
composer install
```

## Tests

Install dependencies as mentioned above (which will resolve [PHPUnit](http://packagist.org/packages/phpunit/phpunit)), then you can run the test suite:

```bash
./vendor/bin/phpunit
```

Or to run an individual test file:

```bash
./vendor/bin/phpunit tests/Biller/GatewayTest.php
```

## License
Biller is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)