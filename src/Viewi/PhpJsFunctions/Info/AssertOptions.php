<?php

namespace Viewi\PhpJsFunctions\Info;

use Viewi\JsTranspile\BaseFunction;

class AssertOptions extends BaseFunction
{
    public static string $name = 'assert_options';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'AssertOptions.js';
        return file_get_contents($jsToInclude);
    }
}
