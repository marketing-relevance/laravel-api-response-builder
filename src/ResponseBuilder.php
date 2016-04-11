<?php

namespace MarcinOrlowski\ResponseBuilder;

/**
 * Laravel API Response Builder
 *
 * @package   MarcinOrlowski\ResponseBuilder
 *
 * @author    Marcin Orlowski <mail@marcinorlowski.com>
 * @copyright 2016 Marcin Orlowski
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/MarcinOrlowski/laravel-api-response-builder
 */

use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Builds standardized \Symfony\Component\HttpFoundation\Response response object
 *
 * @package MarcinOrlowski\ResponseBuilder
 */
class ResponseBuilder
{
	/**
	 * Creates standarised API response array. If you set APP_DEBUG to true, 'code_hex' field will be additionally added to reported JSON for easier manual debugging.
	 *
	 * @param integer $code    response code (not http response code)
	 * @param string  $message error message or 'OK'
	 * @param array   $data    api response data if any
	 *
	 * @return array response array ready to be encoded as json and sent back to client
	 */
	protected static function buildResponseArray($code, $message, array $data = null) {
		$response = ['success' => ($code == ErrorCodes::OK),
		             'code'    => $code,
		             'locale'  => \App::getLocale(),
		             'message' => $message,
		             'data'    => (object)$data,
		];

		return $response;
	}

	/**
	 * Returns success
	 *
	 * @param array|null $data        payload to be returned as 'data' node, @null if none
	 * @param int        $http_code   HTTP return code to be set for this response (HttpResponse::HTTP_OK (200) is default)
	 * @param array      $lang_args   array of arguments passed to Lang if message associated with error_code uses placeholders
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function success(array $data = null, $http_code = HttpResponse::HTTP_OK, array $lang_args = []) {
		return static::buildSuccessResponse($data, ErrorCodes::OK, $http_code, $lang_args);
	}

	/**
	 * Returns success with custom HTTP code
	 *
	 * @param int $http_code HTTP return code to be set for this response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function successWithHttpCode($http_code) {
		return static::buildSuccessResponse(null, ErrorCodes::OK, $http_code, []);
	}

	/**
	 * Returns success with payload and custom HTTP code
	 *
	 * @param array|null $data      payload to be returned as 'data' node
	 * @param int        $http_code HTTP return code to be set for this response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function successWithDataAndHttpCode(array $data, $http_code) {
		return static::buildSuccessResponse($data, ErrorCodes::OK, $http_code, []);
	}

	/**
	 * @param array|null $data        payload to be returned as 'data' node, @null if none
	 * @param int        $return_code numeric code to be returned as 'code' @\App\ErrorCodes::OK is default
	 * @param int        $http_code   HTTP return code to be set for this response (DEFAULT_OK_HTTP_CODE (200) is default)
	 * @param array      $lang_args   array of arguments passed to Lang if message associated with error_code uses placeholders
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected static function buildSuccessResponse(array $data = null, $return_code = ErrorCodes::OK, $http_code = self::DEFAULT_ERROR_HTTP_CODE, array $lang_args = []) {
		if( is_null($http_code) ) {
			$http_code = HttpResponse::HTTP_OK;
		}

		if( is_int($return_code) === false ) {
			throw new \InvalidArgumentException('error_code must be integer');
		} else {
			if( is_int($http_code) === false ) {
				throw new \InvalidArgumentException('http_code must be integer');
			} else {
				if( $http_code < 199 ) {
					throw new \InvalidArgumentException('http_code cannot be lower than 200');
				} else {
					if( $http_code > 299 ) {
						throw new \InvalidArgumentException('http_code cannot be higher than 299');
					}
				}
			}
		}

		return static::make($return_code, $return_code, $data, $http_code, $lang_args);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error message uses placeholders as well as return data payload
	 *
	 * @param int        $error_code internal error code with matching error message
	 * @param array|null $lang_args  if array, then this passed as arguments to Lang::get() to build final string.
	 * @param array|null $data       payload array to be returned in 'data' node or response object
	 * @param int|null   $http_code  optional HTTP status code to be used with this response. Default @DEFAULT_ERROR_HTTP_CODE
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function error($error_code, $lang_args = [], $data = null, $http_code = HttpResponse::HTTP_BAD_REQUEST) {
		return static::buildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	/**
	 * @param int        $error_code numeric code to be returned as 'code'
	 * @param array|null $data       payload to be returned as 'data' node, @null if none
	 * @param array|null $lang_args  |null optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithData($error_code, $data, array $lang_args = []) {
		return static::buildErrorResponse($data, $error_code, HttpResponse::HTTP_BAD_REQUEST, $lang_args);
	}

	/**
	 * @param int        $error_code numeric code to be returned as 'code'
	 * @param array|null $data       payload to be returned as 'data' node, @null if none
	 * @param int        $http_code  HTTP error code to be returned with this Response
	 * @param array|null $lang_args  |null optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithDataAndHttpCode($error_code, $data, $http_code, array $lang_args = []) {
		return static::buildErrorResponse($data, $error_code, $http_code, $lang_args);
	}

	/**
	 * @param int        $error_code numeric code to be returned as 'code'
	 * @param int        $http_code  HTTP return code to be set for this response (HttpResponse::HTTP_OK (200) is default)
	 * @param array|null $lang_args  |null optional array with arguments passed to Lang::get()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithHttpCode($error_code, $http_code, $lang_args = []) {
		return static::buildErrorResponse(null, $error_code, $http_code, $lang_args);
	}

	/**
	 * @param int      $error_code numeric code to be returned as 'code'
	 * @param string   $message    custom message to be returned as part of error response
	 * @param int|null $http_code  optional HTTP status code to be used with this response. Default @DEFAULT_ERROR_HTTP_CODE
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function errorWithMessage($error_code, $message, $http_code = HttpResponse::HTTP_BAD_REQUEST) {
		return static::buildErrorResponse(null, $error_code, $http_code, [], $message);
	}

	/**
	 * Builds error Response object. Supports optional arguments passed to Lang::get() if associated error message uses placeholders as well as return data payload
	 *
	 * @param int         $error_code internal error code with matching error message
	 * @param array|null  $lang_args  if array, then this passed as arguments to Lang::get() to build final string.
	 * @param array|null  $data       payload array to be returned in 'data' node or response object
	 * @param int|null    $http_code  optional HTTP status code to be used with this response. Default @DEFAULT_ERROR_HTTP_CODE
	 * @param string|null $message    custom message to be returned as part of error response
	 * @param array       $headers    optional HTTP headers to be returned in error response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected static function buildErrorResponse($data, $error_code, $http_code, $lang_args, $message = null, array $headers = []) {
		if( is_null($http_code) ) {
			$http_code = HttpResponse::HTTP_BAD_REQUEST;
		}
		if( is_null($message) ) {
			$message = $error_code;
		}

		if( is_int($error_code) === false ) {
			throw new \InvalidArgumentException('error_code must be integer');
		} elseif( $error_code == ErrorCodes::OK ) {
			throw new \InvalidArgumentException('error_code must be equal to ErrorCode::OK');
		} elseif( (is_array($lang_args) === false) && (is_null($lang_args) === false) ) {
			throw new \InvalidArgumentException('lang_args must be either array or null');
		} elseif( (is_array($data) === false) && (is_null($data) === false) ) {
			throw new \InvalidArgumentException('data must be either array or null');
		} elseif( is_int($http_code) === false ) {
			throw new \InvalidArgumentException('http_code must be integer');
		} elseif( $http_code < 400 ) {
			throw new \InvalidArgumentException('http_code cannot be lower than 400');
		}

		return static::make($error_code, $message, $data, $http_code, $lang_args, $headers);
	}


	/**
	 * @param int        $return_code                    internal message code (usually 0 for OK, and unique int for errors)
	 * @param string|int $message_message_or_return_code error message string or error code (message will be then obtained from lang/... via ErrorCode class' mapping)
	 * @param array|null $data                           optional additional data to be included in response object
	 * @param int        $http_code                      return HTTP code for build Response object
	 * @param array      $lang_args                      |null optional array with arguments passed to Lang::get()
	 * @param array      $headers                        |null optional HTTP headers to be returned in error response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected static function make($return_code, $message_message_or_return_code, $data, $http_code, $lang_args = [], $headers = []) {
		if( is_null($lang_args) ) {
			$lang_args = [];
		}
		if( is_null($headers) ) {
			$headers = [];
		}

		if( is_string($message_message_or_return_code) === false ) {
			if( is_int($message_message_or_return_code) ) {
				// TODO below line should most likely be in separate LangHelper class but since it would be the only line here, it ends here with this annotation :)
				$key = ErrorCodes::getMapping($message_message_or_return_code);
				if( is_null($key) ) {
					$message_message_or_return_code = \Lang::get(ErrorCodes::getMapping(ErrorCodes::NO_ERROR_DESCRIPTION), ['error_code' => $message_message_or_return_code]);
				} else {
					$message_message_or_return_code = \Lang::get($key, $lang_args);
				}
			} else {
				throw new \InvalidArgumentException('Message must be either string or resolvable error code');
			}
		}

		return Response::json(static::buildResponseArray($return_code, $message_message_or_return_code, $data), $http_code, $headers);
	}
}