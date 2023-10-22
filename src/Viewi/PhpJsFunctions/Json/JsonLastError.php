<?php

namespace Viewi\PhpJsFunctions\Json;

use Viewi\JsTranspile\BaseFunction;

class JsonLastError extends BaseFunction
{
    public static string $name = 'json_last_error';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'JsonLastError.js';
        return file_get_contents($jsToInclude);
    }
}
