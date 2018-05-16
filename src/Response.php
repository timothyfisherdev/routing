<?php

namespace Routing;

use InvalidArgumentException;

/**
 * Represents a response from the dispatcher.
 *
 * This is not to be confused with the PSR-7 response.
 * 
 * This is basically a sort of "payload" object that
 * encapsulates the results of a dispatch.
 */
class Response implements ResponseAware
{
	/**
	 * HTTP response status.
	 * 
	 * @var integer
	 */
	protected $status;

	/**
	 * The matched route.
	 * 
	 * @var Route
	 */
	protected $route;

	/**
	 * List of allowed methods when 405.
	 * 
	 * @var array
	 */
	protected $allowedMethods;

	/**
	 * Build the response.
	 * 
	 * @param int        $status         HTTP response status.
	 * @param Route|null $route          Matched route object
	 * @param array|null $allowedMethods Methods allowed for 405
	 */
	public function __construct(int $status, Route $route = null, array $allowedMethods = null)
	{
		if (!in_array($status, self::STATUSES)) {
			throw new InvalidArgumentException(sprintf(
				'Invalid argument "status", expected one of: %s',
				rtrim(implode(', ', self::STATUSES))
			));
		}

		$this->status = $status;
		$this->route = $route;
		$this->allowedMethods = $allowedMethods;
	}

	/**
	 * Get the HTTP response status.
	 * 
	 * @return integer
	 */
	public function getStatus() : int
	{
		return $this->status;
	}

	/**
	 * Get the matched route object.
	 * 
	 * @return Route
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * Used for when the HTTP response status is 405.
	 *
	 * Shows which methods are allowed on a requested route
	 * when the URL matches but the method does not.
	 * 
	 * @return array
	 */
	public function getAllowedMethods()
	{
		return $this->allowedMethods;
	}
}