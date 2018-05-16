<?php

namespace Routing;

/**
 * Representation of a route.
 *
 * Holds the methods allowed on the route, the pattern of the route, 
 * the handler that corresponds to it, and any route parameters.
 *
 * The handler can be anything, it's up to the user what data they want
 * to attach to it and how they want to interpret it.
 */
class Route
{
	/**
	 * Methods allowed on the route.
	 * 
	 * @var array
	 */
	protected $methods = [];

	/**
	 * The handler.
	 * 
	 * @var mixed
	 */
	protected $handler;

	/**
	 * User-supplied route pattern.
	 * 
	 * @var string
	 */
	protected $pattern;

	/**
	 * Route parameters/variables.
	 * Specified by: {foo: <regex>}
	 * 
	 * @var array
	 */
	protected $parameters;

	/**
	 * Build the route with its attributes.
	 * 
	 * @param array  $methods Allowed methods.
	 * @param string $pattern Custom pattern.
	 * @param mixed $handler The route handler.
	 */
	public function __construct(array $methods, string $pattern, $handler)
	{
		$this->methods = $methods;
		$this->handler = $handler;
		$this->pattern = $pattern;
	}

	/**
	 * Get allowed methods.
	 * 
	 * @return array
	 */
	public function getMethods() : array
	{
		return $this->methods;
	}

	/**
	 * Get the handler associated with the route.
	 * 
	 * @return mixed
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Get the pattern associates with the route.
	 * 
	 * @return string
	 */
	public function getPattern() : string
	{
		return $this->pattern;
	}

	/**
	 * Set the parameters names/values for the route.
	 * 
	 * @param array $parameters name => value
	 */
	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * Gets the route parameters.
	 * 
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}
}