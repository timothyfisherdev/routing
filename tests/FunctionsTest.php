<?php

namespace Routing\Test;

use Routing\Map;
use Routing\Route;
use Routing\Dispatcher;
use PHPUnit\Framework\TestCase;

/**
 * Test the helper functions.
 */
class FunctionsTest extends TestCase
{
	/**
	 * Test that the createDispatcher function
	 * creates a Dispatcher.
	 */
	public function testCreateDispatcher()
	{
		require dirname(__DIR__) . '/src/functions.php';

		$dispatcher = \Routing\createDispatcher(function(Map $map) {
			$map->addRoute('GET', '/foo', 'bar');
		});

		$this->assertInstanceOf(Dispatcher::class, $dispatcher);
		$this->assertAttributeCount(1, 'routes', $dispatcher);
	}
}