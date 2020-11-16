<?php

    namespace Viewi\JsFunctions\Functions\Arrays;

    use Viewi\JsFunctions\BaseFunctionConverter;
    use Viewi\JsTranslator;

    class ArrayUintersectUassoc extends BaseFunctionConverter
    {
        public static string $name = 'array_uintersect_uassoc';
    
        public static function convert(
            JsTranslator $translator,
            string $code,
            string $identation
        ): string {
            $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUintersectUassoc.js';
            $translator->includeJsFile(self::$name, $jsToInclue);
            return $code . '(';
        }
    }
