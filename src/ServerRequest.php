<?php
/**
 * Mutable Http Message
 *
 * @author  David Lundgren
 * @package mutable-http-message
 */
namespace Muhm\Http;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Mutable ServerRequest
 *
 * @package Muhm\Http
 */
class ServerRequest
	extends Request
	implements ServerRequestInterface
{
	/**
	 * @var array Server parameters
	 */
	private $serverParams = [];

	/**
	 * @var array cookie parameters
	 */
	private $cookieParams = [];

	/**
	 * @var array query parameters
	 */
	private $queryParams = [];

	/**
	 * @var array file parameters
	 */
	private $fileParams = [];

	/**
	 * @var mixed parsed body
	 */
	private $parsedBody;

	/**
	 * @var array attributes
	 */
	private $attributes = [];

	/**
	 * Retrieve server parameters.
	 *
	 * Retrieves data related to the incoming request environment,
	 * typically derived from PHP's $_SERVER superglobal. The data IS NOT
	 * REQUIRED to originate from $_SERVER.
	 *
	 * @return array
	 */
	public function getServerParams()
	{
		return $this->serverParams;
	}

	/**
	 * Retrieve cookies.
	 *
	 * Retrieves cookies sent by the client to the server.
	 *
	 * The data MUST be compatible with the structure of the $_COOKIE
	 * superglobal.
	 *
	 * @return array
	 */
	public function getCookieParams()
	{
		return $this->cookieParams;
	}

	/**
	 * Create a new instance with the specified cookies.
	 *
	 * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
	 * be compatible with the structure of $_COOKIE. Typically, this data will
	 * be injected at instantiation.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return a new instance that has the
	 * updated cookie values.
	 *
	 * @param array $cookies Array of key/value pairs representing cookies.
	 * @return self
	 */
	public function withCookieParams(array $cookies)
	{
		$this->cookieParams = $cookies;

		return $this;
	}

	/**
	 * Retrieve query string arguments.
	 *
	 * Retrieves the deserialized query string arguments, if any.
	 *
	 * Note: the query params might not be in sync with the URL or server
	 * params. If you need to ensure you are only getting the original
	 * values, you may need to parse the composed URL or the `QUERY_STRING`
	 * composed in the server params.
	 *
	 * @return array
	 */
	public function getQueryParams()
	{
		return $this->queryParams;
	}

	/**
	 * Create a new instance with the specified query string arguments.
	 *
	 * These values SHOULD remain immutable over the course of the incoming
	 * request. They MAY be injected during instantiation, such as from PHP's
	 * $_GET superglobal, or MAY be derived from some other value such as the
	 * URI. In cases where the arguments are parsed from the URI, the data
	 * MUST be compatible with what PHP's parse_str() would return for
	 * purposes of how duplicate query parameters are handled, and how nested
	 * sets are handled.
	 *
	 * Setting query string arguments MUST NOT change the URL stored by the
	 * request, nor the values in the server params.
	 *
	 * @param array $query Array of query string arguments, typically from
	 *                     $_GET.
	 * @return self
	 */
	public function withQueryParams(array $query)
	{
		$this->queryParams = $query;

		return $this;
	}

	/**
	 * Retrieve the upload file metadata.
	 *
	 * This method MUST return file upload metadata in the same structure
	 * as PHP's $_FILES superglobal.
	 *
	 * These values MUST remain immutable over the course of the incoming
	 * request. They SHOULD be injected during instantiation, such as from PHP's
	 * $_FILES superglobal, but MAY be derived from other sources.
	 *
	 * @return array Upload file(s) metadata, if any.
	 */
	public function getFileParams()
	{
		return $this->fileParams;
	}

	/**
	 * Retrieve any parameters provided in the request body.
	 *
	 * If the request Content-Type is either application/x-www-form-urlencoded
	 * or multipart/form-data, and the request method is POST, this method MUST
	 * return the contents of $_POST.
	 *
	 * Otherwise, this method may return any results of deserializing
	 * the request body content; as parsing returns structured content, the
	 * potential types MUST be arrays or objects only. A null value indicates
	 * the absence of body content.
	 *
	 * @return null|array|object The deserialized body parameters, if any.
	 *     These will typically be an array or object.
	 */
	public function getParsedBody()
	{
		return $this->parsedBody;
	}

	/**
	 * Create a new instance with the specified body parameters.
	 *
	 * These MAY be injected during instantiation.
	 *
	 * If the request Content-Type is either application/x-www-form-urlencoded
	 * or multipart/form-data, and the request method is POST, use this method
	 * ONLY to inject the contents of $_POST.
	 *
	 * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
	 * deserializing the request body content. Deserialization/parsing returns
	 * structured data, and, as such, this method ONLY accepts arrays or objects,
	 * or a null value if nothing was available to parse.
	 *
	 * As an example, if content negotiation determines that the request data
	 * is a JSON payload, this method could be used to create a request
	 * instance with the deserialized parameters.
	 *
	 * @param null|array|object $data The deserialized body data. This will
	 *                                typically be in an array or object.
	 * @return self
	 */
	public function withParsedBody($data)
	{
		if (isset($data) && (!is_array($data) || !is_object($data))) {
			throw new \InvalidArgumentException("Only arrays or objects are allowed");
		}

		$this->parsedBody = $data;

		return $this;
	}

	/**
	 * Retrieve attributes derived from the request.
	 *
	 * The request "attributes" may be used to allow injection of any
	 * parameters derived from the request: e.g., the results of path
	 * match operations; the results of decrypting cookies; the results of
	 * deserializing non-form-encoded message bodies; etc. Attributes
	 * will be application and request specific, and CAN be mutable.
	 *
	 * @return array Attributes derived from the request.
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Retrieve a single derived request attribute.
	 *
	 * Retrieves a single derived request attribute as described in
	 * getAttributes(). If the attribute has not been previously set, returns
	 * the default value as provided.
	 *
	 * This method obviates the need for a hasAttribute() method, as it allows
	 * specifying a default value to return if the attribute is not found.
	 *
	 * @see getAttributes()
	 * @param string $name    The attribute name.
	 * @param mixed  $default Default value to return if the attribute does not exist.
	 * @return mixed
	 */
	public function getAttribute($name, $default = null)
	{
		return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
	}

	/**
	 * Create a new instance with the specified derived request attribute.
	 *
	 * This method allows setting a single derived request attribute as
	 * described in getAttributes().
	 *
	 * @see getAttributes()
	 * @param string $name  The attribute name.
	 * @param mixed  $value The value of the attribute.
	 * @return self
	 */
	public function withAttribute($name, $value)
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * Create a new instance that removes the specified derived request
	 * attribute.
	 *
	 * This method allows removing a single derived request attribute as
	 * described in getAttributes().
	 *
	 * @see getAttributes()
	 * @param string $name The attribute name.
	 * @return self
	 */
	public function withoutAttribute($name)
	{
		if (array_key_exists($name, $this->attributes)) {
			unset($this->attributes[$name]);
		}

		return $this;
	}
}
