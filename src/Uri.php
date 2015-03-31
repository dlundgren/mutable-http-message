<?php
/**
 * Mutable Http Message
 *
 * @author  David Lundgren
 * @package mutable-http-message
 */
namespace Muhm\Http;

use Psr\Http\Message\UriInterface;

/**
 * Mutable Uri
 *
 * @note This implementation allows more than http/https...
 *
 * @package Muhm\Http
 */
class Uri
	implements UriInterface
{
	/**
	 * @var array List of default scheme ports
	 */
	private $schemePorts = [
		'ssh'    => 22,
		'telnet' => 23,
		'smtp'   => 25,
		'http'   => 80,
		'imap'   => 143,
		'https'  => 443,
	];

	/**
	 * @var array the parsed uri
	 */
	private $parsedUri;

	/**
	 * Constructs the uri
	 *
	 * @param $uri
	 */
	public function __construct($uri)
	{
		$this->parsedUri = parse_url($uri);
	}

	/**
	 * Retrieve the URI scheme.
	 *
	 * Implementations SHOULD restrict values to "http", "https", or an empty
	 * string but MAY accommodate other schemes if required.
	 *
	 * If no scheme is present, this method MUST return an empty string.
	 *
	 * The string returned MUST omit the trailing "://" delimiter if present.
	 *
	 * @return string The scheme of the URI.
	 */
	public function getScheme()
	{
		return isset($this->parsedUri['scheme']) ? str_replace('://', '', $this->parsedUri['scheme']) : '';
	}

	/**
	 * Retrieve the authority portion of the URI.
	 *
	 * The authority portion of the URI is:
	 *
	 * <pre>
	 * [user-info@]host[:port]
	 * </pre>
	 *
	 * If the port component is not set or is the standard port for the current
	 * scheme, it SHOULD NOT be included.
	 *
	 * This method MUST return an empty string if no authority information is
	 * present.
	 *
	 * @return string Authority portion of the URI, in "[user-info@]host[:port]"
	 *     format.
	 */
	public function getAuthority()
	{
		$authority = '';
		$userInfo  = $this->getUserInfo();
		$host      = $this->getHost();
		$port      = $this->getPort();

		!empty($userInfo) && $authority .= "{$userInfo}@";
		!empty($host) && $authority[] .= $host;
		!empty($port) && $authority[] .= ":{$port}";

		return $authority;
	}

	/**
	 * Retrieve the user information portion of the URI, if present.
	 *
	 * If a user is present in the URI, this will return that value;
	 * additionally, if the password is also present, it will be appended to the
	 * user value, with a colon (":") separating the values.
	 *
	 * Implementations MUST NOT return the "@" suffix when returning this value.
	 *
	 * @return string User information portion of the URI, if present, in
	 *     "username[:password]" format.
	 */
	public function getUserInfo()
	{
		$userInfo = '';
		isset($this->parsedUri['user']) && $userInfo .= $this->parsedUri['user'];
		isset($this->parsedUri['pass']) && $userInfo .= ":{$this->parsedUri['pass']}";

		return $userInfo;
	}

	/**
	 * Retrieve the host segment of the URI.
	 *
	 * This method MUST return a string; if no host segment is present, an
	 * empty string MUST be returned.
	 *
	 * @return string Host segment of the URI.
	 */
	public function getHost()
	{
		return isset($this->parsedUri['host']) ? $this->parsedUri['host'] : '';
	}

	/**
	 * Retrieve the port segment of the URI.
	 *
	 * If a port is present, and it is non-standard for the current scheme,
	 * this method MUST return it as an integer. If the port is the standard port
	 * used with the current scheme, this method SHOULD return null.
	 *
	 * If no port is present, and no scheme is present, this method MUST return
	 * a null value.
	 *
	 * If no port is present, but a scheme is present, this method MAY return
	 * the standard port for that scheme, but SHOULD return null.
	 *
	 * @return null|int The port for the URI.
	 */
	public function getPort()
	{
		if (isset($this->parsedUri['port'])) {
			if (empty($this->parsedUri['scheme']) ||
				(isset($this->schemePorts[$this->parsedUri['scheme']]) &&
					$this->parsedUri['port'] != $this->schemePorts[$this->parsedUri['scheme']])
			) {
				return (int)$this->parsedUri['port'];
			}
		}

		return null;
	}

	/**
	 * Retrieve the path segment of the URI.
	 *
	 * This method MUST return a string; if no path is present it MUST return
	 * the string "/".
	 *
	 * @return string The path segment of the URI.
	 */
	public function getPath()
	{
		return isset($this->parsedUri['path']) ? $this->parsedUri['path'] : '/';
	}

	/**
	 * Retrieve the query string of the URI.
	 *
	 * This method MUST return a string; if no query string is present, it MUST
	 * return an empty string.
	 *
	 * The string returned MUST omit the leading "?" character.
	 *
	 * @return string The URI query string.
	 */
	public function getQuery()
	{
		return isset($this->parsedUri['query']) ? $this->parsedUri['query'] : '';
	}

	/**
	 * Retrieve the fragment segment of the URI.
	 *
	 * This method MUST return a string; if no fragment is present, it MUST
	 * return an empty string.
	 *
	 * The string returned MUST omit the leading "#" character.
	 *
	 * @return string The URI fragment.
	 */
	public function getFragment()
	{
		return isset($this->parsedUri['fragment']) ? $this->parsedUri['fragment'] : '';
	}

	/**
	 * Create a new instance with the specified scheme.
	 *
	 * If the scheme provided includes the "://" delimiter, it MUST be removed.
	 *
	 * Implementations SHOULD restrict values to "http", "https", or an empty
	 * string but MAY accommodate other schemes if required.
	 *
	 * An empty scheme is equivalent to removing the scheme.
	 *
	 * @param string $scheme The scheme to use with the new instance.
	 * @return self A new instance with the specified scheme.
	 * @throws \InvalidArgumentException for invalid or unsupported schemes.
	 */
	public function withScheme($scheme)
	{
		if (!empty($scheme)) {
			if (!is_string($scheme)) {
				throw new \InvalidArgumentException("Scheme must be a string");
			}
			$scheme                    = str_replace('://', '', $scheme);
			$this->parsedUri['scheme'] = $scheme;
		}
		elseif (isset($this->parsedUri['scheme'])) {
			unset($this->parsedUri['scheme']);
		}

		return $this;
	}

	/**
	 * Create a new instance with the specified user information.
	 *
	 * Password is optional, but the user information MUST include the
	 * user; an empty string for the user is equivalent to removing user
	 * information.
	 *
	 * @param string      $user     User name to use for authority.
	 * @param null|string $password Password associated with $user.
	 * @return self A new instance with the specified user information.
	 */
	public function withUserInfo($user, $password = null)
	{
		if (!empty($user)) {
			if (!is_string($user)) {
				throw new \InvalidArgumentException("User must be a string");
			}
			if (isset($password) && !is_string($password)) {
				throw new \InvalidArgumentException("Password must be a string");
			}

			$this->parsedUri['user'] = $user;
			isset($password) && ($this->parsedUri['pass'] = $password);
		}
		else {
			if (isset($this->parsedUri['user'])) {
				unset($this->parsedUri['user']);
			}
			if (isset($this->parsedUri['pass'])) {
				unset($this->parsedUri['pass']);
			}
		}

		return $this;
	}

	/**
	 * Create a new instance with the specified host.
	 *
	 * An empty host value is equivalent to removing the host.
	 *
	 * @param string $host Hostname to use with the new instance.
	 * @return self A new instance with the specified host.
	 * @throws \InvalidArgumentException for invalid hostnames.
	 */
	public function withHost($host)
	{
		if (!empty($host)) {
			if (!is_string($host)) {
				throw new \InvalidArgumentException("Host must be a string");
			}

			// @TODO filter_var
			$this->parsedUri['host'] = $host;
		}
		elseif (isset($this->parsedUri['host'])) {
			unset($this->parsedUri['host']);
		}

		return $this;
	}

	/**
	 * Create a new instance with the specified port.
	 *
	 * Implementations MUST raise an exception for ports outside the
	 * established TCP and UDP port ranges.
	 *
	 * A null value provided for the port is equivalent to removing the port
	 * information.
	 *
	 * @param null|int $port Port to use with the new instance; a null value
	 *                       removes the port information.
	 * @return self A new instance with the specified port.
	 * @throws \InvalidArgumentException for invalid ports.
	 */
	public function withPort($port)
	{
		if (isset($port)) {
			if ($port < 1 || $port > 65535) {
				throw new \InvalidArgumentException("Port must be between 1-65535");
			}
			$this->parsedUri['port'] = (int)$port;
		}
		elseif (!empty($this->parsedUri['port'])) {
			unset($this->parsedUri['port']);
		}

		return $this;
	}

	/**
	 * Create a new instance with the specified path.
	 *
	 * The path MUST be prefixed with "/"; if not, the implementation MAY
	 * provide the prefix itself.
	 *
	 * The implementation MUST percent-encode reserved characters as
	 * specified in RFC 3986, Section 2, but MUST NOT double-encode any
	 * characters.
	 *
	 * An empty path value is equivalent to removing the path.
	 *
	 * @param string $path The path to use with the new instance.
	 * @return self A new instance with the specified path.
	 * @throws \InvalidArgumentException for invalid paths.
	 */
	public function withPath($path)
	{
		if (!empty($path)) {
			if (!is_string($path)) {
				throw new \InvalidArgumentException("Path must be a string");
			}

			// force '/' if not there
			$path = ('/' !== $path[0] ? '/' : '') . $path;

			// we need to convert without double-encoding
			// :/?#[]@!$&'()*+,;=

			$this->parsedUri['path'] = rawurlencode($path);
		}
		elseif (!empty($this->parsedUri['path'])) {
			unset($this->parsedUri['path']);
		}

		return $this;
	}

	/**
	 * Create a new instance with the specified query string.
	 *
	 * If the query string is prefixed by "?", that character MUST be removed.
	 * Additionally, the query string SHOULD be parseable by parse_str() in
	 * order to be valid.
	 *
	 * The implementation MUST percent-encode reserved characters as
	 * specified in RFC 3986, Section 2, but MUST NOT double-encode any
	 * characters.
	 *
	 * An empty query string value is equivalent to removing the query string.
	 *
	 * @param string $query The query string to use with the new instance.
	 * @return self A new instance with the specified query string.
	 * @throws \InvalidArgumentException for invalid query strings.
	 */
	public function withQuery($query)
	{
		if (!empty($query)) {
			if (!is_string($query)) {
				throw new \InvalidArgumentException("Query must be a string");
			}

			$query  = ($query[0] === '?' ? substr($query, 1) : $query);
			$parsed = [];
			parse_str($query, $parsed);
			if (empty($parsed)) {
				// assuming that something is wrong as it didn't parse;
				throw new \InvalidArgumentException("Query is not parseable by parse_str");
			}

			$this->parsedUri['query'] = rawurlencode($query);
		}
		elseif (isset($this->parsedUri['query'])) {
			unset($this->parsedUri['query']);
		}

		return $this;
	}

	/**
	 * Create a new instance with the specified URI fragment.
	 *
	 * This method MUST retain the state of the current instance, and return
	 * a new instance that contains the specified URI fragment.
	 *
	 * If the fragment is prefixed by "#", that character MUST be removed.
	 *
	 * An empty fragment value is equivalent to removing the fragment.
	 *
	 * @param string $fragment The URI fragment to use with the new instance.
	 * @return self A new instance with the specified URI fragment.
	 */
	public function withFragment($fragment)
	{
		if (!empty($fragment)) {
			if (!is_string($fragment)) {
				throw new \InvalidArgumentException("Fragment must be a string");
			}

			$fragment                    = ($fragment[0] === '#' ? substr($fragment, 1) : $fragment);
			$this->parsedUri['fragment'] = $fragment;
		}
		elseif (isset($this->parsedUri['fragment'])) {
			unset($this->parsedUri['fragment']);
		}

		return $this;
	}

	/**
	 * Return the string representation of the URI.
	 *
	 * Concatenates the various segments of the URI, using the appropriate
	 * delimiters:
	 *
	 * - If a scheme is present, "://" MUST append the value.
	 * - If the authority information is present, that value will be
	 *   concatenated.
	 * - If a path is present, it MUST be prefixed by a "/" character.
	 * - If a query string is present, it MUST be prefixed by a "?" character.
	 * - If a URI fragment is present, it MUST be prefixed by a "#" character.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$path     = $this->getPath();
		$query    = $this->getQuery();
		$fragment = $this->getFragment();

		$uri = '';

		isset($this->parsedUri['scheme']) && $uri .= "{$this->parsedUri['scheme']}://";
		$uri .= $this->getAuthority();
		!empty($path) && $uri .= ($path[0] !== '/' ? '/' : '') . $path;
		!empty($query) && $uri .= ($query[0] !== '?' ? '?' : '') . $query;
		!empty($fragment) && $uri .= ($fragment[0] !== '#' ? '#' : '') . $fragment;

		return $uri;
	}
}
