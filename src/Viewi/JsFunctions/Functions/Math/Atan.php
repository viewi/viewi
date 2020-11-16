<?php

    namespace Viewi\JsFunctions\Functions\Math;

    use Viewi\JsFunctions\BaseFunctionConverter;
    use Viewi\JsTranslator;

    class Atan extends BaseFunctionConverter
    {
        public static string $name = 'atan';
    
        public static function convert(
            JsTranslator $translator,
            string $code,
            string $identation
        ): string {
            $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Atan.js';
            $translator->includeJsFile(self::$name, $jsToInclue);
            return $code . '(';
        }
    }
