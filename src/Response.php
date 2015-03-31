<?php
/**
 * Mutable Http Message
 *
 * @author  David Lundgren
 * @package mutable-http-message
 */
namespace Muhm\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Mutable Response
 *
 * @package Muhm\Http
 */
class Response
	extends AbstractMessage
	implements ResponseInterface
{
	/**
	 * @var array List of Reason Phrases
	 */
	private $reasonPhrases = [
		/** RFC 7231 */
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Request',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Payload Too Large',
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Range Not Satisfiable',
		417 => 'Expectation Failed',
		426 => 'Upgrade Required',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		/** IANA **/
		102 => 'Processing',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		308 => 'Permanent Redirect',
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
		/** twitter **/
		420 => 'Enhance Your Calm'
	];

	/**
	 * @var string
	 */
	private $reasonPhrase = '';

	/**
	 * @var int The status code
	 */
	private $statusCode = 200;

	/**
	 * Gets the response Status-Code.
	 *
	 * The Status-Code is a 3-digit integer result code of the server's attempt
	 * to understand and satisfy the request.
	 *
	 * @return integer Status code.
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * Create a new instance with the specified status code, and optionally
	 * reason phrase, for the response.
	 *
	 * If no Reason-Phrase is specified, implementations MAY choose to default
	 * to the RFC 7231 or IANA recommended reason phrase for the response's
	 * Status-Code.
	 *
	 * @link http://tools.ietf.org/html/rfc7231#section-6
	 * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 * @param integer     $code         The 3-digit integer result code to set.
	 * @param null|string $reasonPhrase The reason phrase to use with the
	 *                                  provided status code; if none is provided, implementations MAY
	 *                                  use the defaults as suggested in the HTTP specification.
	 * @return self
	 * @throws \InvalidArgumentException For invalid status code arguments.
	 */
	public function withStatus($code, $reasonPhrase = null)
	{
		$this->statusCode   = (int)$code;
		$this->reasonPhrase = $reasonPhrase ?: '';

		if (empty($this->reasonPhrase) && isset($this->reasonPhrases[$this->statusCode])) {
			$this->reasonPhrase = $this->reasonPhrases[$this->statusCode];
		}

		return $this;
	}

	/**
	 * Gets the response Reason-Phrase, a short textual description of the Status-Code.
	 *
	 * Because a Reason-Phrase is not a required element in a response
	 * Status-Line, the Reason-Phrase value MAY be null. Implementations MAY
	 * choose to return the default RFC 7231 recommended reason phrase (or those
	 * listed in the IANA HTTP Status Code Registry) for the response's
	 * Status-Code.
	 *
	 * @link http://tools.ietf.org/html/rfc7231#section-6
	 * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 * @return string|null Reason phrase, or null if unknown.
	 */
	public function getReasonPhrase()
	{
		return $this->reasonPhrase;
	}
}
