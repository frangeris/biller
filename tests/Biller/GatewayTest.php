<?php

namespace Tests\Biller;

use Tests\TestCase;

class GatewayTest extends TestCase
{
	public function testGatewayInitialization()
	{
		$this->assertNull(\Biller\Gateway::me(static::$config));
	}
}