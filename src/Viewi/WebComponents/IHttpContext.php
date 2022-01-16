<?php

namespace Viewi\WebComponents;

interface IHttpContext
{
    function getResponseHeaders(): ?array;

    function setResponseHeader(string $key, string $value): void;

    function getCurrentUrl(): ?string;
}
