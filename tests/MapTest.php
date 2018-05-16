<?php

namespace Routing\Test;

use Routing\Map;
use Routing\Route;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Map object.
 */
class MapTest extends TestCase
{
	/**
	 * Test instantiating the Map object with pre-made
	 * Route objects.
	 */
	public function testInstantiateWithRouteObjects()
	{
		$routes = [new Route([], 'foo', 'bar')];
		$map = new Map(...$routes);

		$this->assertInstanceOf(Map::class, $map);
	}

	/**
	 * Test adding a route to the Map with special
	 * methods pipe syntax.
	 */
	public function testAddRoute()
	{
		$map = new Map();
		$route = $map->addRoute('GET', '/foo', 'bar');
		$this->assertInstanceOf(Route::class, $route);

		$route = $map->addRoute('GET|POST|PUT', '/foo', 'bar');
		$this->assertInstanceOf(Route::class, $route);
		$this->assertEquals(['GET', 'POST', 'PUT'], $route->getMethods());
	}

	/**
	 * Test adding a route group.
	 */
	public function testAddGroup()
	{
		$map = new Map();
		$map->addGroup('/foo', function(Map $map) {
			$map->addRoute('GET|POST|PUT', '/bar', 'baz');
		});

		$route = $map->getRoutes()[0];

		$this->assertEquals('/foo/bar', $route->getPattern());
		$this->assertEquals(['GET', 'POST', 'PUT'], $route->getMethods());
	}

	/**
	 * Test adding nested route groups.
	 */
	public function testNestedAddGroup()
	{
		$map = new Map();
		$map->addGroup('/foo', function(Map $map) {
			$map->addGroup('/bar', function(Map $map) {
				$map->addRoute('GET|POST|PUT', '/baz', 'bim');
			});
		});

		$route = $map->getRoutes()[0];

		$this->assertEquals('/foo/bar/baz', $route->getPattern());
		$this->assertEquals('bim', $route->getHandler());
	}

	/**
	 * Test the clear functionality.
	 */
	public function testClear()
	{
		$map = new Map();
		$map->addGroup('/foo', function(Map $map) {
			$map->addGroup('/bar', function(Map $map) {
				$map->addRoute('GET|POST|PUT', '/baz', 'bim');
			});
		});
		$map->clear();

		$this->assertEquals([], $map->getRoutes());
		$this->assertAttributeEquals('', 'groupPrefix', $map);
	}
}