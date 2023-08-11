<?php


namespace Tests\Unit;

use Exception;
use Tests\Support\UnitTester;
use Viewi\JsTranspile\JsTranspiler;

class JsTranspilerTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    // tests
    public function testBasic()
    {
        $transpiler = new JsTranspiler();
        $jsCode = $transpiler->convert('<?php $a = "Hello World";');
        $this->assertEquals('var a = "Hello World";', trim($jsCode));
    }
}
