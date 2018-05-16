<?php

namespace Routing;

/**
 * Dispatches a request against an array of
 * Route objects.
 *
 * Matches are done with regular expressions.
 */
class Dispatcher implements ResponseAware
{
	/**
	 * Array of Route objects to match against.
	 * 
	 * @var array
	 */
	protected $routes = [];

	/**
	 * Build the dispatcher with the Route objects to
	 * match against.
	 * 
	 * @param array $routes Array of Route objects.
	 */
	public function __construct(Route ...$routes)
	{
		$this->routes = $routes;
	}

	/**
	 * Dispatches a request.
	 *
	 * Accepts a request method and a URL. The Route
	 * objects are looped through and first checked
	 * for a matching allowed method.
	 *
	 * If the method is matched, the pattern of the route
	 * goes on to be matched.
	 *
	 * If there was no match, we loop back through the routes
	 * so that we can determine if it was a 404 or a 405.
	 *
	 * If it's a 405 we gather the methods that are allowed
	 * on the route URL pattern.
	 * 
	 * @param  string $method The requested HTTP method.
	 * @param  string $url    The requested URL.
	 * 
	 * @return Response
	 */
	public function dispatch($method, $url) : Response
	{
		foreach ($this->routes as $route) {
			if (!$this->matchMethod($method, $route->getMethods())) {
				continue;
			}

			if (!$this->matchPattern($url, $route)) {
				continue;
			}

			return $this->respond(self::FOUND, $route);
		}

		if ($method === 'HEAD') {
			return $this->dispatch('GET', $url);
		}

		return $this->getAllowed($method, $url);
	}

	/**
	 * Loops back through all of the Route objects to 
	 * determine whether the route does not exist,
	 * or if the method is just not allowed.
	 *
	 * @param  string $requestedMethod The requested HTTP method.
	 * @param  string $url             The requested URL.
	 * 
	 * @return Response
	 */
	protected function getAllowed($requestedMethod, $url) : Response
	{
		$allowed = [];

		foreach ($this->routes as $route) {
			foreach ($route->getMethods() as $httpMethod) {
				if ($requestedMethod === $httpMethod) {
					continue;
				}

				if ($this->matchPattern($url, $route)) {
					$allowed[] = $httpMethod;
				}
			}
		}

		if (!empty($allowed)) {
			return $this->respond(self::METHOD_NOT_ALLOWED, null, $allowed);
		}

		return $this->respond(self::NOT_FOUND);
	}

	/**
	 * Does the matching of the pattern.
	 *
	 * This is done with regular expressions.
	 *
	 * First, the pattern that the user specified on the route
	 * is converted into a regular expression that we can
	 * match against. That is, only if it has parameters.
	 *
	 * If there are no parameters then we just compare the
	 * strings.
	 *
	 * If there's a match we set the parameter values on the
	 * Route object and return true, false otherwise.
	 * 
	 * @param  string $requestedUrl The requested URL.
	 * @param  Route  $route        The Route object to match against.
	 * 
	 * @return boolean				True if matched, false otherwise.
	 */
	protected function matchPattern($requestedUrl, Route $route) : bool
	{
		$routePattern = '/' . trim($route->getPattern(), '/');

		if ($routePattern === '*' || $requestedUrl === $routePattern) {
			return true;
		}

		list($regex, $vars) = $this->buildRegex($routePattern);

		if (preg_match('~^' . $regex . '$~', $requestedUrl, $matches)) {
			$parameters = [];

			foreach ($vars as $key => $value) {
				$parameters[$key] = array_key_exists($key, $matches) 
					? urldecode($matches[$key]) 
					: null;
			}

			$route->setParameters($parameters);

			return true;
		}

		return false;
	}

	/**
	 * Checks if the requested method is allowed on a route.
	 * 
	 * @param  string $requestedMethod The requested HTTP method.
	 * @param  array  $routeMethods    The methods allowed on a Route.
	 * 
	 * @return bool                    True if allowed, false otherwise.
	 */
	protected function matchMethod($requestedMethod, array $routeMethods) : bool
	{
		return in_array($requestedMethod, $routeMethods);
	}

	/**
	 * Builds the regex from a custom Route pattern.
	 *
	 * First, we check for any optional segments that are
	 * specified with parentheses "()". We can replace the 
	 * last parentheses with a ")?" to make that entire segment
	 * an optional regex pattern.
	 *
	 * We also replace any "/*" wildcards at the end of a route
	 * with a regex pattern that matches anything except for 
	 * line breaks.
	 *
	 * In addition, we get to retrieve the parameter names on the
	 * route here since we use preg_replace_callback to match on 
	 * parameters delimited by "{}".
	 * 
	 * @param  string $pattern The custom Route pattern.
	 * 
	 * @return array           [regex, parameters['param' => null]]
	 */
	protected function buildRegex($pattern) : array
	{
		$pattern = str_replace([')', '/*'], [')?', '(?|/?|/.*?)'], $pattern);

		$vars = [];

		return [preg_replace_callback(
			'~{([a-zA-Z][a-zA-Z0-9_-]*)(?::([^{}]*))?}~',
			function ($matches) use (&$vars) {
				$vars[$matches[1]] = null;
				$regex = isset($matches[2]) ? trim($matches[2]) : '[^/]+';

				return '(?P<' . $matches[1] . '>' . $regex . ')';
			},
			$pattern
		), $vars];
	}

	/**
	 * Helper method for building and returning a 
	 * Response object.
	 * 
	 * @param  int        $status         The HTTP response status.
	 * @param  Route|null $route          The matched Route.
	 * @param  array|null $allowedMethods If 405, any allowed methods.
	 * 
	 * @return Response
	 */
	protected function respond($status, $route = null, $allowedMethods = null) : Response
	{
		return new Response($status, $route, $allowedMethods);
	}
}