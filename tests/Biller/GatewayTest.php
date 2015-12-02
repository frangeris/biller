<?php

namespace Tests\Biller;

use Tests\Biller\TestCase;

class GatewayTest extends TestCase
{
	public function testGatewayInitialization()
	{
		$this->assertNull(\Biller\Gateway::me(static::$config));
	}
}