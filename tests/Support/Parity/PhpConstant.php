<?php

namespace Tests\Support\Parity;

use Viewi\JsTranspile\JsTranspiler;

/**
 * A PHP constant as a case argument: new PhpConstant('STR_PAD_LEFT').
 * PHP receives constant('STR_PAD_LEFT'); JS receives whatever the transpiler emits for the
 * name (asked, not assumed): the value for a built-in constant, or the build error.
 */
final class PhpConstant
{
    public function __construct(public readonly string $name)
    {
    }

    /** The JS expression the transpiler emits for this constant; throws its build error. */
    public function jsExpression(): string
    {
        $output = (new JsTranspiler())->convert('<?php ' . $this->name . ';');
        if ($output->errorMessage !== null) {
            return 'undefined /* ' . str_replace('*/', '', $output->errorMessage) . ' */';
        }
        return rtrim(trim((string)$output), ';');
    }
}
