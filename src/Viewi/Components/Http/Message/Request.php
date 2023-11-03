<?php

namespace Viewi\Components\Http\Message;

class Request
{
    public function __construct(public string $url, public string $method = 'get', public array $headers = [], public $body = null)
    {
    }

    public function withMethod(string $method): self
    {
        $clone = $this->clone($this);
        $clone->method = $method;
        return $clone;
    }

    public function withUrl(string $url): self
    {
        $clone = $this->clone($this);
        $clone->url = $url;
        return $clone;
    }

    public function withHeaders(array $headers): self
    {
        $clone = $this->clone($this);
        $clone->headers = array_merge($clone->headers, $headers);
        return $clone;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = $this->clone($this);
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function withBody($body = null): self
    {
        $clone = $this->clone($this);
        $clone->body = $body;
        return $clone;
    }

    // PHP clone is not supported in JS, use custom clone implementation
    public function clone(): self
    {
        $clone = new Request($this->url, $this->method, $this->headers, $this->body);
        return $clone;
    }
}
