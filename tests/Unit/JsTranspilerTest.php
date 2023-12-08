<?php


namespace Tests\Unit;

use Exception;
use Tests\Support\UnitTester;
use Viewi\JsTranspile\JsTranspiler;

class JsTranspilerTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;
    protected JsTranspiler $transpiler;

    protected function _before()
    {
        $this->transpiler = new JsTranspiler();
    }
    // tests
    public function testBasic()
    {
        $jsCode = $this->transpiler->convert('<?php $a = "Hello World";');
        $this->assertEquals('var a = "Hello World";', trim($jsCode));
    }
}
