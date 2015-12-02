<?php

namespace Tests\Biller;

class TestCase extends \PHPUnit_Framework_TestCase
{
    const API_KEY = 'tGN0bIwXnHdwOa85VABjPdSn8nWY7G7I';

    protected static $config;

    protected static $mock;

    public function setup()
    {
        static::$config = require __DIR__.'/../config.php';

        // append biller conf
        static::$config['biller'] = [
            'key' => self::API_KEY,
            'custom_id' => 'id',
            'custom_email' => 'email',
        ];

        $di = new \Phalcon\DI\FactoryDefault();
        $di->set('config', static::$config);

        $di->set('db', function () {
            return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                'host' => '127.0.0.1',
                'username' => 'root',
                'password' => '',
                'dbname' => 'biller_tests',
                'charset' => 'utf8',
            ));
        });

        // Session manager
        $di->setShared('session', function () {
            $session = new \Phalcon\Session\Adapter\Files();
            $session->start();

            return $session;
        });

        self::authorize();
        static::$mock = new \Tests\User();
    }

    protected static function authorize()
    {
        \Stripe\Stripe::setApiKey(self::API_KEY);
    }

    protected static function coupon($id)
    {
        return \Stripe\Coupon::create([
            'id' => $id.'-'.\Phalcon\Text::random(5),
            'duration' => 'forever',
            'percent_off' => 25,
        ]);
    }
}
