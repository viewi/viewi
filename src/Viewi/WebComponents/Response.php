<?php

namespace Viewi\WebComponents;

class Response
{
    public array $Headers = [];
    public int $StatusCode = 200;
    public string $StatusText = '';
    public $Content = '';
    public bool $Stringify = false;

    static function Json($data)
    {
        $response = new Response();
        $response->Content = $data;
        $response->Stringify = true;
        $response->Headers['Content-type'] = 'application/json; charset=utf-8';
        return $response;
    }

    static function Html($data)
    {
        $response = new Response();
        $response->Content = $data;
        $response->Headers['Content-type'] = 'text/html; charset=utf-8';
        return $response;
    }

    static function Text($data)
    {
        $response = new Response();
        $response->Content = $data;
        $response->Headers['Content-type'] = 'text/text; charset=utf-8';
        return $response;
    }

    static function File($data, $mimeType = null)
    {
        $response = new Response();
        $response->Content = $data;
        if ($mimeType !== null) {
            $response->Headers['Content-type'] = $mimeType;
        }
        return $response;
    }

    function WithCode(int $code)
    {
        $this->StatusCode = $code;
        return $this;
    }

    function WithHeaders(array $headers)
    {
        $this->Headers += $headers;
        return $this;
    }

    function WithContent($data)
    {
        $this->Content = $data;
        return $this;
    }

    function AsJson()
    {
        $this->Stringify = true;
        $this->Headers['Content-type'] = 'application/json; charset=utf-8';
        return $this;
    }

    function AsPlainText()
    {
        $this->Stringify = false;
        $this->Headers['Content-type'] = 'text/text; charset=utf-8';
        return $this;
    }

    function AsHtml()
    {
        $this->Stringify = false;
        $this->Headers['Content-type'] = 'text/html; charset=utf-8';
        return $this;
    }

    function WithContentType(string $contentType)
    {
        $this->Headers['Content-type'] = $contentType;
        return $this;
    }
}
