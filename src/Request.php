<?php
/**
 * Mutable Http Message
 *
 * @author  David Lundgren
 * @package mutable-http-message
 */
namespace Muhm\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Mutable Request
 *
 * @package Muhm\Http
 */
class Request
	extends AbstractMessage
	implements RequestInterface
{
	/**
	 * @var string The HTTP request Target
	 */
	private $requestTarget;

	/**
	 * @var string The HTTP Request method
	 */
	private $method;

	/**
	 * @var UriInterface The URI
	 */
	private $uri;

	/**
	 * @var array List of valid HTTP methods
	 */
	private $validMethods = [
		'OPTIONS',
		'GET',
		'HEAD',
		'POST',
		'PUT',
		'DELETE',
		'TRACE',
		'CONNECT',
		'PATCH'
	];

	/**
	 * Extends MessageInterface::getHeaders() to provide request-specific
	 * behavior.
	 *
	 * Retrieves all message headers.
	 *
	 * This method acts exactly like MessageInterface::getHeaders(), with one
	 * behavioral change: if the Host header has not been previously set, the
	 * method MUST attempt to pull the host segment of the composed URI, if
	 * present.
	 *
	 * @see MessageInterface::getHeaders()
	 * @see UriInterface::getHost()
	 * @return array Returns an associative array of the message's headers. Each
	 *     key MUST be a header name, and each value MUST be an array of strings.
	 */
	public function getHeaders()
	{
		$this->forceHostHeader();

		return parent::getHeaders();
	}

	/**
	 * Extends MessageInterface::getHeader() to provide request-specific
	 * behavior.
	 *
	 * This method acts exactly like MessageInterface::getHeader(), with
	 * one behavioral change: if the Host header is requested, but has
	 * not been previously set, the method MUST attempt to pull the host
	 * segment of the composed URI, if present.
	 *
	 * @see MessageInterface::getHeader()
	 * @see UriInterface::getHost()
	 * @param string $name Case-insensitive header field name.
	 * @return string
	 */
	public function getHeader($name)
	{
		strtolower($name) === 'host' && $this->forceHostHeader();

		return $this->getHeader($name);
	}

	/**
	 * Extends MessageInterface::getHeaderLines() to provide request-specific
	 * behavior.
	 *
	 * Retrieves a header by the given case-insensitive name as an array of strings.
	 *
	 * This method acts exactly like MessageInterface::getHeaderLines(), with
	 * one behavioral change: if the Host header is requested, but has
	 * not been previously set, the method MUST attempt to pull the host
	 * segment of the composed URI, if present.
	 *
	 * @see MessageInterface::getHeaderLines()
	 * @see UriInterface::getHost()
	 * @param string $name Case-insensitive header field name.
	 * @return string[]
	 */
	public function getHeaderLines($name)
	{
		strtolower($name) === 'host' && $this->forceHostHeader();

		return $this->getHeaderLines($name);
	}

	/**
	 * Retrieves the message's request target.
	 *
	 * Retrieves the message's request-target either as it will appear (for
	 * clients), as it appeared at request (for servers), or as it was
	 * specified for the instance (see withRequestTarget()).
	 *
	 * In most cases, this will be the origin-form of the composed URI,
	 * unless a value was provided to the concrete implementation (see
	 * withRequestTarget() below).
	 *
	 * If no URI is available, and no request-target has been specifically
	 * provided, this method MUST return the string "/".
	 *
	 * @return string
	 */
	public function getRequestTarget()
	{
		if (isset($this->requestTarget)) {
			return $this->requestTarget;
		}

		if (empty($this->uri)) {
			return '/';
		}

		$query = $this->uri->getQuery();

		return ($this->uri->getPath() ?: '/') . ($query ? "?{$query}" : '');
	}

	/**
	 * Modifies the instance with a specific request-target.
	 *
	 * If the request needs a non-origin-form request-target — e.g., for
	 * specifying an absolute-form, authority-form, or asterisk-form —
	 * this method may be used to create an instance with the specified
	 * request-target, verbatim.
	 *
	 * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
	 *     request-target forms allowed in request messages)
	 * @param mixed $requestTarget
	 * @return self
	 */
	public function withRequestTarget($requestTarget)
	{
		if (('*' === $requestTarget) || // asterisk-form
			preg_match('#(/[^\s]+|([a-z]\.){2,}:[0-9]+)#i', $requestTarget) || // origin-form | authority-form
			filter_var($requestTarget, FILTER_VALIDATE_URL) // absolute-form
		) {
			$this->requestTarget = $requestTarget;

			return $this;
		}

		throw new \InvalidArgumentException("Request Target must be in one of absolute-form, authority-form, asterisk-form or origin-form");
	}

	/**
	 * Retrieves the HTTP method of the request.
	 *
	 * @return string Returns the request method.
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Modifies the instance with the provided HTTP method.
	 *
	 * While HTTP method names are typically all uppercase characters, HTTP
	 * method names are case-sensitive and thus implementations SHOULD NOT
	 * modify the given string.
	 *
	 * @param string $method Case-insensitive method.
	 * @return self
	 * @throws \InvalidArgumentException for invalid HTTP methods.
	 */
	public function withMethod($method)
	{
		$method = strtoupper($method);
		if (!isset($this->validMethods[$method])) {
			throw new \InvalidArgumentException("Invalid HTTP method: $method");
		}
		$this->method = $method;

		return $this;
	}

	/**
	 * Retrieves the URI instance.
	 *
	 * This method MUST return a UriInterface instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 * @return UriInterface Returns a UriInterface instance
	 *     representing the URI of the request, if any.
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Modifies the instance with the provided URI.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 * @param UriInterface $uri New request URI to use.
	 * @return self
	 */
	public function withUri(UriInterface $uri)
	{
		$this->uri = $uri;

		return $this;
	}

	/**
	 * Ensure the host header is set
	 */
	private function forceHostHeader()
	{
		if (!$this->hasHeader('host') && isset($this->uri)) {
			$this->withHeader('host', $this->uri->getHost());
		}
	}
}
