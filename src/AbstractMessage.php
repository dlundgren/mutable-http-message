<?php
/**
 * Mutable Http Message
 *
 * @author  David Lundgren
 * @package mutable-http-message
 */
namespace Muhm\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamableInterface;

/**
 * Represents an object that extends the MessageInterface
 *
 * @package Muhm\Http
 */
abstract class AbstractMessage
	implements MessageInterface
{
	/**
	 * @var string The HTTP protocol version
	 */
	private $protocolVersion = '1.1';

	/**
	 * @var array The HTTP headers
	 */
	private $headers;

	/**
	 * @var StreamableInterface The Body
	 */
	private $body;

	/**
	 * Retrieves the HTTP protocol version as a string.
	 *
	 * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
	 *
	 * @return string HTTP protocol version.
	 */
	public function getProtocolVersion()
	{
		return $this->protocolVersion;
	}

	/**
	 * Modifies the instance with the specified HTTP protocol version.
	 *
	 * The version string MUST contain only the HTTP version number (e.g.,
	 * "1.1", "1.0").
	 *
	 * @param string $version HTTP protocol version
	 * @return self
	 */
	public function withProtocolVersion($version)
	{
		$this->protocolVersion = $version;

		return $this;
	}

	/**
	 * Retrieves all message headers.
	 *
	 * The keys represent the header name as it will be sent over the wire, and
	 * each value is an array of strings associated with the header.
	 *
	 *     // Represent the headers as a string
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         echo $name . ": " . implode(", ", $values);
	 *     }
	 *
	 *     // Emit headers iteratively:
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         foreach ($values as $value) {
	 *             header(sprintf('%s: %s', $name, $value), false);
	 *         }
	 *     }
	 *
	 * While header names are not case-sensitive, getHeaders() will preserve the
	 * exact case in which headers were originally specified.
	 *
	 * @return array Returns an associative array of the message's headers. Each
	 *     key MUST be a header name, and each value MUST be an array of strings.
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Checks if a header exists by the given case-insensitive name.
	 *
	 * @param string $name Case-insensitive header field name.
	 * @return bool Returns true if any header names match the given header
	 *                     name using a case-insensitive string comparison. Returns false if
	 *                     no matching header name is found in the message.
	 */
	public function hasHeader($name)
	{
		return array_key_exists(strtolower($name), $this->headers);
	}

	/**
	 * Retrieve a header by the given case-insensitive name, as a string.
	 *
	 * This method returns all of the header values of the given
	 * case-insensitive header name as a string concatenated together using
	 * a comma.
	 *
	 * NOTE: Not all header values may be appropriately represented using
	 * comma concatenation. For such headers, use getHeaderLines() instead
	 * and supply your own delimiter when concatenating.
	 *
	 * If the header did not appear in the message, this method should return
	 * a null value.
	 *
	 * @param string $name Case-insensitive header field name.
	 * @return string|null
	 */
	public function getHeader($name)
	{
		$lines = $this->getHeaderLines($name);

		return $lines ? join(',', $lines) : null;
	}

	/**
	 * Retrieves a header by the given case-insensitive name as an array of strings.
	 *
	 * If the header did not appear in the message, this method should return an
	 * empty array.
	 *
	 * @param string $name Case-insensitive header field name.
	 * @return string[]
	 */
	public function getHeaderLines($name)
	{
		return $this->hasHeader($name) ? $this->headers[strtolower($name)] : [];
	}

	/**
	 * Modifies the instance with the provided header, replacing any existing
	 * values of any headers with the same case-insensitive name.
	 *
	 * While header names are case-insensitive, the casing of the header will
	 * be preserved by this function, and returned from getHeaders().
	 *
	 * @param string          $name  Case-insensitive header field name.
	 * @param string|string[] $value Header value(s).
	 * @return self
	 * @throws \InvalidArgumentException for invalid header names or values.
	 */
	public function withHeader($name, $value)
	{
		$this->validateHeaderValues($value);

		$name                 = strtolower($name);
		$this->headers[$name] = (array)$value;

		return $this;
	}

	/**
	 * Modifies the instance, with the specified header appended with the
	 * given value.
	 *
	 * Existing values for the specified header will be maintained. The new
	 * value(s) will be appended to the existing list. If the header did not
	 * exist previously, it will be added.
	 *
	 * @param string          $name  Case-insensitive header field name to add.
	 * @param string|string[] $value Header value(s).
	 * @return self
	 * @throws \InvalidArgumentException for invalid header names or values.
	 */
	public function withAddedHeader($name, $value)
	{
		$this->validateHeaderValues($value);

		$name = strtolower($name);
		if (!$this->hasHeader($name)) {
			$this->headers[$name] = [];
		}

		$this->headers[$name] = array_merge($this->headers[$name], (array)$value);

		return $this;
	}

	/**
	 * Modifies the instance, without the specified header.
	 *
	 * Header resolution MUST be done without case-sensitivity.
	 *
	 * @param string $name Case-insensitive header field name to remove.
	 * @return self
	 */
	public function withoutHeader($name)
	{
		if ($this->hasHeader($name)) {
			unset($this->headers[strtolower($name)]);
		}

		return $this;
	}

	/**
	 * Gets the body of the message.
	 *
	 * @return StreamableInterface Returns the body as a stream.
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Modifies the instance, with the specified message body.
	 *
	 * The body MUST be a StreamableInterface object.
	 *
	 * @param StreamableInterface $body Body.
	 * @return self
	 * @throws \InvalidArgumentException When the body is not valid.
	 */
	public function withBody(StreamableInterface $body)
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * Validates that the header values are a string or array of strings
	 *
	 * @param $values
	 */
	private function validateHeaderValues($values)
	{
		if (!is_string($values) || !is_array($values)) {
			throw new \InvalidArgumentException("Header values must be a string or array of strings");
		}

		if (is_array($values)) {
			foreach($values as $value) {
				if (!is_string($value)) {
					throw new \InvalidArgumentException("Header values must be a string or array of strings");
				}
			}
		}
	}
}
