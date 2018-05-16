<?php

namespace Routing;

use Routing\Map;
use Routing\Dispatcher;

if (!function_exists('Routing\createDispatcher')) {
	/**
	 * Helper function to create a dispatcher.
	 *
	 * Accepts a callback which is passed a Map object
	 * used to add routes.
	 * 
	 * The callback is executed which adds the Route 
	 * objects to the Map. The Route objects are unpacked
	 * from the Map and passed to the Dispatcher that is
	 * returned.
	 * 
	 * @param  callable $mapCallback Callable that adds routes.
	 * 
	 * @return Dispatcher
	 */
	function createDispatcher(callable $mapCallback)
	{
		$map = new Map();
		$mapCallback($map);

		return new Dispatcher(...$map->getRoutes());
	}
}