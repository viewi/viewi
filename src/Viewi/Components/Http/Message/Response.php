<?php

namespace Viewi\Components\Http\Message;

class Response
{
    public function __construct(public string $url, public int $status, public string $statusText, public array $headers = [], public $body = null)
    {
    }

    public function withUrl(string $url): self
    {
        $clone = $this->clone($this);
        $clone->url = $url;
        return $clone;
    }

    public function withStatus(int $status, ?string $statusText = null): self
    {
        $clone = $this->clone($this);
        $clone->status = $status;
        if ($statusText !== null) {
            $clone->statusText = $statusText;
        }
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

    public function ok(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    // PHP clone is not supported in JS, use custom clone implementation
    public function clone(): self
    {
        $clone = new Response($this->url, $this->status, $this->statusText, $this->headers, $this->body);
        return $clone;
    }
}
