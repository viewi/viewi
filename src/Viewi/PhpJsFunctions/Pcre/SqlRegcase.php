<?php

namespace Viewi\PhpJsFunctions\Pcre;

use Viewi\JsTranspile\BaseFunction;

class SqlRegcase extends BaseFunction
{
    public static string $name = 'sql_regcase';

    public static function getUses(): array
    {
        return ['setlocale'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'SqlRegcase.js';
        return file_get_contents($jsToInclude);
    }
}
