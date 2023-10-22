<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Printf extends BaseFunction
{
    public static string $name = 'printf';

    public static function getUses(): array
    {
        return ['sprintf', 'echo'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Printf.js';
        return file_get_contents($jsToInclude);
    }
}
