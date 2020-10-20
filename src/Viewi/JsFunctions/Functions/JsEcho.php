<?php

namespace Viewi\JsFunctions\Functions;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class JsEcho extends BaseFunctionConverter
{
    public static bool $directive = true;

    public static string $name = 'echo';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $code = substr($code, 0, -4);
        $code .= $identation . 'console.log(';
        $code .= $translator->readCodeBlock(';');
        // $translator->SkipToTheSymbol(';');
        $code .= ')';

        return $code;
    }
}
