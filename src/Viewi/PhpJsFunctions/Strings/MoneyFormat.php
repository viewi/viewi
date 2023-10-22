<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class MoneyFormat extends BaseFunction
{
    public static string $name = 'money_format';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'MoneyFormat.js';
        return file_get_contents($jsToInclude);
    }
}
