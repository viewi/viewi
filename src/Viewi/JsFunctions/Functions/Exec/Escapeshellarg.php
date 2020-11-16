<?php

    namespace Viewi\JsFunctions\Functions\Exec;

    use Viewi\JsFunctions\BaseFunctionConverter;
    use Viewi\JsTranslator;

    class Escapeshellarg extends BaseFunctionConverter
    {
        public static string $name = 'escapeshellarg';
    
        public static function convert(
            JsTranslator $translator,
            string $code,
            string $identation
        ): string {
            $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Escapeshellarg.js';
            $translator->includeJsFile(self::$name, $jsToInclue);
            return $code . '(';
        }
    }
