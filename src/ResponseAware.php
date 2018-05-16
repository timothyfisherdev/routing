<?php

namespace Routing;

/**
 * Traits can't have constants.
 *
 * A simple interface that associates status codes
 * to HTTP response names.
 *
 * We keep an array of the status codes for quick
 * lookup.
 */
interface ResponseAware
{
	/**
	 * HTTP 404 - Not Found.
	 */
	const NOT_FOUND = 0;

	/**
	 * HTTP 200 - OK.
	 */
	const FOUND = 1;

	/**
	 * HTTP 405 - Method Not Allowed.
	 */
	const METHOD_NOT_ALLOWED = 2;

	/**
	 * For fast lookups.
	 */
	const STATUSES = [0, 1, 2];
}