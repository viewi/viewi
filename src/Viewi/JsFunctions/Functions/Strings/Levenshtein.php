<?php

    namespace Viewi\JsFunctions\Functions\Strings;

    use Viewi\JsFunctions\BaseFunctionConverter;
    use Viewi\JsTranslator;

    class Levenshtein extends BaseFunctionConverter
    {
        public static string $name = 'levenshtein';
    
        public static function convert(
            JsTranslator $translator,
            string $code,
            string $identation
        ): string {
            $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Levenshtein.js';
            $translator->includeJsFile(self::$name, $jsToInclue);
            return $code . '(';
        }
    }
