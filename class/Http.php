<?php
class Http {
	
	/**
	 * Http response versions
	 * 
	 * @var String
	 */
	const VERSION = "1.1";
	
	/**
	 * HTTP code constants
	 * 
	 * @var Integers
	 */
	const OK = 200;
	const CREATED = 201;
	const NO_CONTENT = 204;
	const SEE_OTHER = 303;
	const BAD_REQUEST = 400;
	const UAUTHORIZED = 401;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const UNPROCESSABLE_ENTITY = 422;
	const ITS_FUCKING_BORK = 500;

	/**
	 * Http status codes and coresponding text
	 * 
	 * @static
	 * @var array
	 */
	static $codes = array(
		'200' => '200 OK',
		'201' => '201 Created',
		'204' => '204 No Content',
		'303' => '303 See Other',
		'400' => '400 Bad Request',
		'401' => '401 Unauthorized',
		'404' => '404 Not Found',
		'405' => '405 Method Not Allowed',
		'422' => '422 Unprocessable Entity',
		'500' => '500 Internal error'
	);


	/**
	 * Returns the HTTP Status code header
	 * 
	 * @static
	 * @access public
	 * @param Int $code
	 */
	static function Response($code) {
		return 'HTTP/' . Http::VERSION . ' ' . Http::$codes[$code];
	}
	
}