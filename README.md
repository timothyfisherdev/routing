# A simple routing library
[![Build Status](https://travis-ci.org/timothyfisherdev/routing.svg?branch=master)](https://travis-ci.org/timothyfisherdev/routing) [![Coverage Status](https://coveralls.io/repos/github/timothyfisherdev/routing/badge.svg?branch=master)](https://coveralls.io/github/timothyfisherdev/routing?branch=master)

This is a very simple routing library for PHP. It is regular expression based and matches routes one at a time. For a much faster routing library check out [FastRoute](https://github.com/nikic/FastRoute). This library does not implement the fancy way of combining routes into one regular expression, but maybe in the future.

This was mainly created for learning and educational purposes, and for getting more comfortable with PHPUnit, Travis, and Coveralls.


# Docs
### Setup
This routing library provides a convenient helper function `Routing\createDispatcher()` for creating a `Routing\Dispatcher` object, which dispatches requests with a HTTP method and URL:

    $dispatcher = Routing\createDispatcher(function(Routing\Map $r) {
		$r->addRoute('GET', '/foo', 'fooHandler');
	});
	
	$dispatcher->dispatch('GET', '/foo');

As you can see, the `Routing\createDispatcher()` function accepts a callable callback that is passed a `Routing\Map` object. The Map object is used to collect routes for the dispatcher using `Routing\Map::addRoute()`.

This method accepts three arguments:

 1. The HTTP method(s) separated by a pipe (|).
 2. The route pattern to match against.
 3. The route handler.

Multiple HTTP methods may be specified as follows:

    $map = new Routing\Map();
    $map->addRoute('GET|POST|PUT', '/foo', 'fooHandler');
The route pattern uses a special syntax for specifying how routes should be matched:

 -  `{}` specifies a dynamic route parameter.
 - `{<name>:<regex>}` specifies a custom regex pattern for a parameter.
 - `()` specifies an optional route segment.
 - `*` and `/*` specify wildcard segments that will match everything.

Route groups are also supported with `Routing\Map::addGroup`, in which a group of routes is prefixed with a specified string. This method accepts the prefix as the first argument, and a callable callback that is passed the Map object:

    $map = new Routing\Map();
    $map->addGroup('/foo', function(Routing\Map $r) {
		$r->addRoute('GET', '/bar', 'handlerFooBar');
	});

	// adds: '/foo/bar'
Nested route groups are also supported, in which all of the previous route groups are appended to each other:

    $map = new Routing\Map();
    $map->addGroup('/foo', function(Routing\Map $r) {
	    $r->addGroup('/bar', function(Routing\Map $r) {
		    $r->addRoute('GET', '/baz', 'handlerFooBarBaz');
	    });
    });

	// adds: '/foo/bar/baz'
### Dispatching
The `Routing\Dispatcher::dispatch()` method accepts two arguments, the requested method, and the requested URL.
> **Note** that this routing library does not care what data is used for the requested method, URL, or route handler. That means that you could dispatch a request on a "FOO" method if you wanted. It's up to the framework/end user to interpret and restrict this if they wish.

The dispatch method will always return a `Routing\Response` object, which is essentially a "payload" with the dispatch results. This library uses a simple `Routing\ResponseAware` interface for mapping the following HTTP response names to status codes:

 - HTTP 404 - Not Found = 0
 - HTTP 200 - OK = 1
 - HTTP 405 - Method Not Allowed = 2

The response object will contain:

 - The status code of the dispatch
 - The matched `Routing\Route` object if 200
 - An array of allowed methods if 405

These results can be retrieved with `Routing\Response::getStatus()`, `Routing\Response::getRoute()`, and `Routing\Response::getAllowedMethods()` respectively.

> **Note** the handler is stored in the Route object, and can be retrieved with: `Routing\Response::getRoute()->getHandler();`

The matched route object is also populated with any parameters and handler for the matched URL. The API for the route object is as follows:

 - `Routing\Route::getMethods()` - The methods allowed on the route
 - `Routing\Route::getHandler()` - The route handler
 - `Routing\Route::getPattern()` - The custom pattern for the route
 - `Routing\Route::setParameters()` - Used to set parameters on the route (this is used by the `Routing\Dispatcher` object to set parameter values when matched)
 - `Routing\Route::getParameters()` - Retrieves the array of parameters and their values

Again, it is up to you (or the end user) to interpret the response and do what you wish with the results.
