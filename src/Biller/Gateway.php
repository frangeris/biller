<?php

namespace Biller;

class Gateway
{
    /**
     * Initializer
     *
     * @param \Phalcon\Config $config Phalcon config
     * @return bool
     */
    public static function me(\Phalcon\Config $config)
    {
        // validate biller in config
        if (!isset($config['biller']))
        {
            throw new \Exception('Entry for "biller" not found on config');
        }

        // validate stripe key
        if (!isset($config['biller']['key']))
        {
            throw new \Exception('Stripe key not found on config');
        }

        // setup the stripe key
        \Stripe\Stripe::setApiKey($config['biller']['key']);

        // throw exceptions for customer, subscriptions models
        \Phalcon\Mvc\Model::setup(['exceptionOnFailedSave' => true]);
    }
}
