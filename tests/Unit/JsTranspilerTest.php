<?php


namespace Tests\Unit;

use Exception;
use Tests\Support\UnitTester;
use Viewi\JsTranspile\ExportItem;
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

    public function testServiceOutput()
    {
        $jsOutput = $this->transpiler->convert(
            <<<'php'
            <?php

            namespace Components\Services\Middleware;

            use Viewi\Components\Middleware\IMIddleware;
            use Viewi\Components\Middleware\IMIddlewareContext;
            use Viewi\DI\Singleton;

            #[Singleton]
            class MermberGuard implements IMIddleware
            {
                public function run(IMIddlewareContext $c)
                {
                    $c->next();
                }
            }
            php
        );
        $this->assertEquals(
            $this->normalizeString(
                <<<'javascript'
                class MermberGuard {
                    run(c) {
                        var $this = this;
                        c.next();
                    }
                }
                javascript
            ),
            $this->normalizeString($jsOutput->__toString())
        );
    }

    public function testServiceExports()
    {
        $jsOutput = $this->transpiler->convert(
            <<<'php'
            <?php

            namespace Components\Services\Middleware;

            use Viewi\Components\Middleware\IMIddleware;
            use Viewi\Components\Middleware\IMIddlewareContext;
            use Viewi\DI\Singleton;

            #[Singleton]
            class MermberGuard implements IMIddleware
            {
                public function run(IMIddlewareContext $c)
                {
                    $c->next();
                }
            }
            php
        );

        // exports
        $exports = $jsOutput->getExports();
        $this->assertCount(1, $exports);
        $middlewareKey = 'Components\Services\Middleware';
        $this->assertArrayHasKey($middlewareKey, $exports);
        /**
         * @var ExportItem
         */
        $middleware = $exports[$middlewareKey];
        $this->assertEquals($middlewareKey, $middleware->Name);
        $this->assertEquals(ExportItem::Namespace, $middleware->Type);
        $this->assertCount(1, $middleware->Children);
        $guardKey = 'MermberGuard';
        $this->assertArrayHasKey($guardKey, $middleware->Children);
        $guard = $middleware->Children[$guardKey];
        $this->assertEquals($guardKey, $guard->Name);
        $this->assertEquals(ExportItem::Class_, $guard->Type);
        // attributes
        $this->assertCount(2, $guard->Attributes);
        $this->assertEquals('Components\Services\Middleware', $guard->Attributes['namespace']);
        $this->assertEqualsCanonicalizing(['Singleton' => ['Singleton']], $guard->Attributes['attrs']);
        // method
        $this->assertCount(1, $guard->Children);
        $runKey = 'run';
        $this->assertArrayHasKey($runKey, $guard->Children);
        $method = $guard->Children[$runKey];
        $this->assertEquals($runKey, $method->Name);
        $this->assertEquals(ExportItem::Method, $method->Type);
    }

    public function testServiceUses()
    {
        $jsOutput = $this->transpiler->convert(
            <<<'php'
            <?php

            namespace Components\Services\Middleware;

            use Viewi\Components\Middleware\IMIddleware;
            use Viewi\Components\Middleware\IMIddlewareContext;
            use Viewi\DI\Singleton;

            #[Singleton]
            class MermberGuard implements IMIddleware
            {
                public function run(IMIddlewareContext $c)
                {
                    $c->next();
                }
            }
            php
        );

        // uses
        $uses = $jsOutput->getUses();
        $this->assertCount(3, $uses);
        $this->assertArrayHasKey('IMIddleware', $uses);
        $this->assertArrayHasKey('IMIddlewareContext', $uses);
        $this->assertArrayHasKey('Singleton', $uses);
    }

    private function normalizeString(string $input): string
    {
        $output = trim(str_replace(["\n", "\r"], [PHP_EOL, ''], $input));
        return $output;
    }
}
