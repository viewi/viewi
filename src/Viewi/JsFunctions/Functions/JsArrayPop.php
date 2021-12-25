<?php

namespace Viewi\JsFunctions\Functions;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsFunctions\BreakCondition;
use Viewi\JsTranslator;

class JsArrayPop extends BaseFunctionConverter
{
    public static string $name = 'array_pop';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $code = substr($code, 0, -9);

        $breakOn = new BreakCondition();
        $breakOn->Keyword = ')';
        $breakOn->ParenthesisNormal = 0;
        $code .= $translator->readCodeBlock($breakOn);
        $translator->skipToTheSymbol(')');
        $code .= '.pop()';
        $translator->activateReactivity([$translator->latestVariablePath, "'pop'"]);

        return $code;
    }
}
