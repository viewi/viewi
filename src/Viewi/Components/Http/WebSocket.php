<?php

namespace Viewi\Components\Http;

use Viewi\Builder\Attributes\Skip;

#[Skip]
abstract class WebSocket
{

    /**
     * 
     * @param string | URL $url 
     * @param string | string[] | null $protocols 
     * @return WebSocket 
     */
    public abstract function __construct($url, $protocols = null);

    /**
     * "arraybuffer" | "blob"
     */
    public string $binaryType = "arraybuffer";
    /**
     * Returns the number of bytes of application data (UTF-8 text and binary data) that have been queued using send() but not yet been transmitted to the network.
     *
     * If the WebSocket connection is closed, this attribute's value will only increase with each call to the send() method. (The number does not reset to zero once the connection closes.)
     *
     * [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/bufferedAmount)
     */
    public int $bufferedAmount;
    /**
     * Returns the extensions selected by the server, if any.
     *
     * [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/extensions)
     */
    public string $extensions;
    /** [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/close_event) */
    public $onclose;
    /** [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/error_event) */
    public $onerror;
    /** [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/message_event) */
    public $onmessage;
    /** [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/open_event) */
    public $onopen;
    /**
     * Returns the subprotocol selected by the server, if any. It can be used in conjunction with the array form of the constructor's second argument to perform subprotocol negotiation.
     *
     * [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/protocol)
     */
    public string $protocol;
    /**
     * Returns the state of the WebSocket object's connection. It can have the values described below.
     *
     * [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/readyState)
     */
    public int $readyState;
    /**
     * Returns the URL that was used to establish the WebSocket connection.
     *
     * [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/url)
     */
    public string $url;
    /**
     * Closes the WebSocket connection, optionally using code as the the WebSocket connection close code and reason as the the WebSocket connection close reason.
     *
     * [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/close)
     */
    public  abstract function close(?int $code, ?string $reason): void;
    /**
     * Transmits data using the WebSocket connection. data can be a string, a Blob, an ArrayBuffer, or an ArrayBufferView.
     *
     * [MDN Reference](https://developer.mozilla.org/docs/Web/API/WebSocket/send)
     * 
     * string | ArrayBufferLike | Blob | ArrayBufferView
     */
    public abstract function send($data): void;
    public const CONNECTING = 0;
    public const OPEN = 1;
    public const CLOSING = 2;
    public const CLOSED = 3;
    public abstract  function addEventListener(string $type, callable $listener, $options = null): void;
    public abstract function removeEventListener(string $type, callable $listener, $options = null): void;
    public abstract function dispatchEvent($event): bool;
}
