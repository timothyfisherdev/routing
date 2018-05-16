<?php

namespace Routing\Test;

use Routing\Response;
use PHPUnit\Framework\TestCase;

/**
 * Test the Response object.
 */
class ResponseTest extends TestCase
{
	/**
	 * Test that the createDispatcher function
	 * creates a Dispatcher.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidStatus()
	{
		$response = new Response(9999);
	}
}