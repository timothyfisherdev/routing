<?php

namespace Routing\Test;

use Routing\Route;
use Routing\Response;
use Routing\Dispatcher;
use PHPUnit\Framework\TestCase;

/**
 * Test the Dispatcher.
 */
class DispatcherTest extends TestCase
{
	/**
	 * Provide some tests with Route objects.
	 */
	public function routesProvider()
	{
		return [
			[[
				new Route(['GET'], '/user/{name}/{id:\d+}', 'handler1'),
				new Route(['GET'], '/foofoo', 'handlerFoo'),
				new Route(['GET'], '/user/{id:\d+}', 'handler2'),
				new Route(['GET', 'POST'], '/user/{name}', 'handler3')
			]]
		];
	}

	/**
	 * Test dispatching a 200 OK route.
	 * 
	 * @dataProvider routesProvider
	 */
	public function testDispatchFound(array $routes)
	{
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('POST', '/user/timothy');
		$route = $response->getRoute();

		$this->assertInstanceOf(Response::class, $response);
		$this->assertInstanceOf(Route::class, $route);
		$this->assertEquals(1, $response->getStatus());
		$this->assertEquals(['name' => 'timothy'], $route->getParameters());
		$this->assertEquals('handler3', $route->getHandler());
	}

	/**
	 * Test dispatching a 404 not found route.
	 * 
	 * @dataProvider routesProvider
	 */
	public function testDispatchNotFound(array $routes)
	{
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('GET', '/foo');

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(0, $response->getStatus());
		$this->assertEquals(null, $response->getRoute());
	}

	/**
	 * Test dispatching a 405 method not allowed route.
	 * 
	 * @dataProvider routesProvider
	 */
	public function testDispatchMethodNotAllowed(array $routes)
	{
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('PUT', '/user/timothy');

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(2, $response->getStatus());
		$this->assertEquals(['GET', 'POST'], $response->getAllowedMethods());
	}

	/**
	 * Test dispatching a static route (doesn't go through regex matching).
	 *
	 * @dataProvider routesProvider
	 */
	public function testDispatchStaticRoute(array $routes)
	{
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('GET', '/foofoo');

		$this->assertEquals(1, $response->getStatus());
	}

	/**
	 * Test that when we dispatch a HEAD request that we end up
	 * falling back to a GET request if no HEAD route was defined.
	 * 
	 * @dataProvider routesProvider
	 */
	public function testDispatchHeadRequestFallsBackToGet(array $routes)
	{
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('HEAD', '/foofoo');
		$route = $response->getRoute();

		$this->assertInstanceOf(Response::class, $response);
		$this->assertInstanceOf(Route::class, $route);
		$this->assertEquals(1, $response->getStatus());
		$this->assertEquals(null, $route->getParameters());
		$this->assertEquals('handlerFoo', $route->getHandler());
	}

	/**
	 * Test trimming of the route pattern.
	 */
	public function testDispatchTrimPatternWorks()
	{
		$routes = [new Route(['GET'], '/////foo////', 'handler1')];
		$dispatcher = new Dispatcher(...$routes);
		$response1 = $dispatcher->dispatch('GET', '/foo');
		$response2 = $dispatcher->dispatch('GET', '/foo/');
		$route1 = $response1->getRoute();
		$route2 = $response2->getRoute();

		$this->assertEquals(1, $response1->getStatus());
	}

	/**
	 * Test dispatching without a '/' prefix in the custom route pattern.
	 */
	public function testDispatchWorksWithoutSlashPrefix()
	{
		$routes = [
			new Route(['GET'], 'foo', 'handler1'),
			new Route(['GET'], 'bar/', 'handler2')
		];
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('GET', '/foo');
		$route = $response->getRoute();

		$this->assertEquals(1, $response->getStatus());
	}

	/**
	 * Test dispatching against a route that matches everything.
	 */
	public function testDispatchWildcardOnly()
	{
		$routes = [new Route(['GET'], '*', 'handler1')];
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('GET', 'foo');

		$this->assertEquals(1, $response->getStatus());
	}

	/**
	 * Test the optional segment functionality delimited with '()'.
	 */
	public function testDispatchWithOptionalSegments()
	{
		$routes = [
			new Route(['GET'], 'foo/foo(bar)', 'handler1')
		];
		$dispatcher = new Dispatcher(...$routes);
		$response1 = $dispatcher->dispatch('GET', '/foo/foo');
		$response2 = $dispatcher->dispatch('GET', '/foo/foobar');

		$this->assertEquals(1, $response1->getStatus());
		$this->assertEquals(1, $response2->getStatus());
	}

	/**
	 * Test nested optional segment functionality.
	 */
	public function testDispatchWithNestedOptionalSegments()
	{
		$routes = [
			new Route(['GET', 'POST'], '/foo(/baz(/bim))', 'handler1')
		];
		$dispatcher = new Dispatcher(...$routes);
		$response1 = $dispatcher->dispatch('POST', '/foo/baz/bim');
		$response2 = $dispatcher->dispatch('GET', '/foo/baz');
		$response3 = $dispatcher->dispatch('GET', '/foo');
		$response4 = $dispatcher->dispatch('PUT', '/foo');
		$response5 = $dispatcher->dispatch('POST', '/foo/bazbim');

		$this->assertEquals(1, $response1->getStatus());
		$this->assertEquals(1, $response2->getStatus());
		$this->assertEquals(1, $response3->getStatus());
		$this->assertEquals(2, $response4->getStatus());
		$this->assertEquals(0, $response5->getStatus());
	}

	/**
	 * Test the wildcard functionality when the wildcard is at 
	 * the end of the route.
	 */
	public function testDispatchWithWildcardEnding()
	{
		$routes = [
			new Route(['GET'], '/foo/bar', 'handler1'),
			new Route(['GET', 'POST'], '/foo/*', 'handler2')
		];
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('GET', '/foo/baz');
		$route = $response->getRoute();

		$this->assertEquals(1, $response->getStatus());
		$this->assertEquals('handler2', $route->getHandler());
	}

	/**
	 * Test the wildcard functionality when the wildcard is in the
	 * middle of the route.
	 */
	public function testDispatchWithWildcardInMiddleOfRouteWorks()
	{
		$routes = [
			new Route(['GET'], '/foo/bar', 'handler1'),
			new Route(['GET', 'POST'], '/foo/*/{baz}', 'handler2')
		];
		$dispatcher = new Dispatcher(...$routes);
		$response = $dispatcher->dispatch('GET', '/foo/bar/baz');
		$route = $response->getRoute();

		$this->assertEquals(1, $response->getStatus());
		$this->assertEquals(['baz' => 'baz'], $route->getParameters());
	}
}