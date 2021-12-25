<?php

namespace Viewi\JsFunctions\Functions;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsFunctions\BreakCondition;
use Viewi\JsTranslator;

class JsCount extends BaseFunctionConverter
{
    public static string $name = 'count';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $code = substr($code, 0, -5);

        $breakOn = new BreakCondition();
        $breakOn->Keyword = ')';
        $breakOn->ParenthesisNormal = 0;
        $code .= $translator->readCodeBlock($breakOn);
        $translator->skipToTheSymbol(')');
        $code .= '.length';
        return $code;
    }
}
