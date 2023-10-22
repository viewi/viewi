<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Join extends BaseFunction
{
    public static string $name = 'join';

    public static function getUses(): array
    {
        return ['implode'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Join.js';
        return file_get_contents($jsToInclude);
    }
}
