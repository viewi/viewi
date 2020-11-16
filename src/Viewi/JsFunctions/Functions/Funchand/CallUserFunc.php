<?php

    namespace Viewi\JsFunctions\Functions\Funchand;

    use Viewi\JsFunctions\BaseFunctionConverter;
    use Viewi\JsTranslator;

    class CallUserFunc extends BaseFunctionConverter
    {
        public static string $name = 'call_user_func';
    
        public static function convert(
            JsTranslator $translator,
            string $code,
            string $identation
        ): string {
            $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'CallUserFunc.js';
            $translator->includeJsFile(self::$name, $jsToInclue);
            return $code . '(';
        }
    }
