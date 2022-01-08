<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayWalkRecursive extends BaseFunctionConverter
{
    public static string $name = 'array_walk_recursive';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayWalkRecursive.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
