<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ChunkSplit extends BaseFunctionConverter
{
    public static string $name = 'chunk_split';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ChunkSplit.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
