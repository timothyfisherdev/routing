<?php

namespace Routing;

/**
 * Used to collect routes and provide an easier
 * syntax to build Route objects.
 */
class Map
{
	/**
	 * Route groups.
	 * 
	 * @var array
	 */
	protected $routes = [];

	/**
	 * The active group prefix.
	 * 
	 * @var string
	 */
	protected $groupPrefix = '';

	/**
	 * Build the map.
	 *
	 * Accepts an optional array of route objects via 
	 * argument unpacking.
	 * 
	 * @param array $routes Array of route objects.
	 */
	public function __construct(Route ...$routes)
	{
		$this->routes = $routes;
	}

	/**
	 * Add a route.
	 *
	 * Creates a route object out of user supplied
	 * arguments.
	 * 
	 * @param string $methods String of methods: 'GET|POST|ETC'
	 * @param string $pattern Custom route pattern to match.
	 * @param mixed $handler  The route handler.
	 */
	public function addRoute($methods, $pattern, $handler)
	{
		$pattern = $this->groupPrefix . $pattern;
		$methods = array_map('strtoupper', explode('|', $methods));
		$pattern = trim($pattern);

		return $this->routes[] = new Route($methods, $pattern, $handler);
	}

	/**
	 * Adds a route group.
	 *
	 * Nested groups work.
	 *
	 * Works by grabbing the original group prefix which is just
	 * a string. We add the group prefix that was specified by appending
	 * what was previously there with what was requested.
	 *
	 * If this is the first route group being added, this would be appending
	 * an empty string to the specified prefix. However, since addGroup would
	 * be called inside of itself:
	 *
	 * $r->addGroup('foo', function($r) {
	 *     $r->addGroup();
	 * });
	 *
	 * The foo prefix will be added before the nested addGroup is called,
	 * so when the nested addGroup is called, it will have access to the
	 * foo prefix.
	 * 
	 * @param string   $prefix   The group prefix.
	 * @param callable $callback The callable callback to add routes in.
	 */
	public function addGroup($prefix, callable $callback)
	{
		$previousPrefix = $this->groupPrefix;
		$this->groupPrefix = $previousPrefix . $prefix;
		$callback($this);
		$this->groupPrefix = $previousPrefix;
	}

	/**
	 * Get the array of route objects.
	 * 
	 * @return array
	 */
	public function getRoutes() : array
	{
		return $this->routes;
	}

	/**
	 * Remove all routes.
	 * 
	 * @return void
	 */
	public function clear()
	{
		$this->routes = [];
		$this->groupPrefix = '';
	}
}