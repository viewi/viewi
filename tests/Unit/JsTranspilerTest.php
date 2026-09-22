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
            class MemberGuard implements IMIddleware
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
                class MemberGuard {
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
            class MemberGuard implements IMIddleware
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
        $guardKey = 'MemberGuard';
        $this->assertArrayHasKey($guardKey, $middleware->Children);
        $guard = $middleware->Children[$guardKey];
        $this->assertEquals($guardKey, $guard->Name);
        $this->assertEquals(ExportItem::Class_, $guard->Type);
        // attributes
        $this->assertCount(3, $guard->Attributes);
        $this->assertEquals('Components\Services\Middleware', $guard->Attributes['namespace']);
        $this->assertEqualsCanonicalizing(['Singleton' => ['Singleton']], $guard->Attributes['attrs']);
        $this->assertEqualsCanonicalizing(['IMIddleware' => 'IMIddleware'], $guard->Attributes['implements']);
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
            class MemberGuard implements IMIddleware
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

    public function testConcatenationCastsLikePhp()
    {
        // PHP's . never adds and prints true/null/floats its own way: non-string operands are cast
        $this->assertEquals(
            'var a = ("x" + _phpCastString(f));',
            trim($this->transpiler->convert('<?php $a = "x" . $f;'))
        );
        $this->assertEquals(
            'var c = (_phpCastString(1) + _phpCastString(2));',
            trim($this->transpiler->convert('<?php $c = 1 . 2;'))
        );
        $this->assertEquals(
            'var h = ("n=" + 5);',
            trim($this->transpiler->convert('<?php $h = "n=" . 5;'))
        );
        $this->assertEquals(
            'var e = (_phpCastString(x) + _phpCastString(y) + "!");',
            trim($this->transpiler->convert('<?php $e = $x . $y . "!";'))
        );
        // parenthesised, so the chain stays one operand
        $this->assertEquals(
            'var w = (_phpCastString(a) + _phpCastString(b))[0];',
            trim($this->transpiler->convert('<?php $w = ($a . $b)[0];'))
        );
        $this->assertEquals(
            's = _phpCastString(s) + _phpCastString(f);',
            trim($this->transpiler->convert('<?php $s .= $f;'))
        );
        $this->assertEquals(
            'var b = ("v: " + _phpCastString(f) + "!");',
            trim($this->transpiler->convert('<?php $b = "v: $f!";'))
        );
    }

    public function testSpaceshipUsesPhpCompare()
    {
        $this->assertEquals(
            'var g = _php_compare(p, q);',
            trim($this->transpiler->convert('<?php $g = $p <=> $q;'))
        );
    }

    public function testInternalHelperUsesAreMarked()
    {
        $uses = $this->transpiler->convert('<?php $a = "x" . $f; $b = strlen($a);')->getUses();
        $this->assertTrue($uses['_phpCastString']->Internal);
        $this->assertFalse($uses['strlen']->Internal);
        // a direct call to the helper is a user call: checked by RestrictedFunctions
        $uses = $this->transpiler->convert('<?php $a = "x" . $f; $b = _phpCastString($a);')->getUses();
        $this->assertFalse($uses['_phpCastString']->Internal);
    }

    public function testBuiltInConstantsAreInlined()
    {
        // the browser has no STR_PAD_LEFT: the value is written in at build time
        $this->assertEquals(
            'var a = str_pad(x, 3, "0", 0);',
            trim($this->transpiler->convert('<?php $a = str_pad($x, 3, "0", STR_PAD_LEFT);'))
        );
        $this->assertEquals(
            'var b = [true, null, false, 128 | 64, Infinity, "/"];',
            trim($this->transpiler->convert('<?php $b = [TRUE, null, False, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, INF, DIRECTORY_SEPARATOR];'))
        );
        // a built-in string constant needs no cast in a concatenation
        $this->assertEquals(
            'var c = ("a" + "\n");',
            trim($this->transpiler->convert('<?php $c = "a" . PHP_EOL;'))
        );
    }

    public function testUnknownConstantFailsInCodeButIsAMemberInTemplates()
    {
        $output = $this->transpiler->convert('<?php $d = MY_APP_CONST;');
        $this->assertStringContainsString("Constant 'MY_APP_CONST' is not a built-in PHP constant", $output->errorMessage);
        // template expression: (click)="runNow" names a component method
        $this->assertEquals('runNow', trim((string)$this->transpiler->convert('runNow', true)));
    }

    public function testEmptyFollowsPhp()
    {
        $this->assertEquals('var a = !_php_cast_bool(b);', trim($this->transpiler->convert('<?php $a = empty($b);')));
        // never throws: the expression is an optional chain, as in isset()
        $this->assertEquals(
            'var c = !_php_cast_bool($this?.items?.["k"]?.[0]);',
            trim($this->transpiler->convert('<?php $c = empty($this->items["k"][0]);'))
        );
        $this->assertTrue($this->transpiler->convert('<?php $a = empty($b);')->getUses()['_php_cast_bool']->Internal);

        // run it: the compiled check against PHP's empty() on the values where JS truthiness differs
        $helpers = \Viewi\PhpJsFunctions\Var\IsObject::getJs() . "\n" . \Viewi\PhpJsFunctions\Helpers\PhpCastBool::getJs();
        $values = ['0', '', '0.0', ' ', 0, 0.0, [], [0], null, 'a'];
        $php = array_map(fn($v) => empty($v), $values);
        $script = $helpers . "\nconst values = " . json_encode($values) . ";\n"
            . "process.stdout.write(JSON.stringify(values.map(function (b) { return " . rtrim(trim((string)$this->transpiler->convert('empty($b)', true)), ';') . "; })));";
        $js = json_decode((string)shell_exec('node -e ' . escapeshellarg($script)), true);
        $this->assertSame($php, $js);
    }

    public function testCastsFollowPhp()
    {
        // parseInt('1e3') is 1 and parseInt(null) NaN; !!'0' is true: the casts go through PHP's rules
        $this->assertEquals(
            'var a = _php_cast_int(x);' . PHP_EOL . 'var b = _php_cast_float(y);' . PHP_EOL
            . 'var c = _phpCastString(n);' . PHP_EOL . 'var d = _php_cast_bool(s);' . PHP_EOL
            . 'var e = _php_cast_array(v);' . PHP_EOL . 'var f = w;',
            trim($this->transpiler->convert('<?php $a = (int)$x; $b = (float)$y; $c = (string)$n; $d = (bool)$s; $e = (array)$v; $f = (object)$w;'))
        );
        $this->assertTrue($this->transpiler->convert('<?php $a = (int)$x;')->getUses()['_php_cast_int']->Internal);
    }

    public function testByReferenceArgumentsAreFilled()
    {
        $js = trim((string)$this->transpiler->convert(
            '<?php namespace T; class ParityRefs { public function run($s) { $list = [3, 1, 2]; sort($list); if (preg_match("/(a)(b)?(c)?/", $s, $m)) { return [$m, $list]; } return [null, $list]; } }'
        ));
        $this->assertStringContainsString('(m = _php_by_ref(m))', $js);
        $this->assertStringContainsString('var m;', $js);

        // run it with the real ports: $m is filled, $list is sorted in place, as in PHP
        $functions = require __DIR__ . '/../../src/Viewi/JsTranspile/functions.php';
        $ported = [];
        $collect = function (string $name) use (&$collect, &$ported, $functions) {
            if (isset($ported[$name])) {
                return;
            }
            $ported[$name] = '';
            foreach ($functions[$name]::getUses() as $dependency) {
                $collect($dependency);
            }
            $ported[$name] = $functions[$name]::getJs();
        };
        foreach (['preg_match', 'sort', '_php_by_ref'] as $name) {
            $collect($name);
        }
        $script = '"use strict";' . "\n" . implode("\n", $ported) . "\n" . $js . "\n"
            . 'const refs = new ParityRefs();' . "\n"
            . 'process.stdout.write(JSON.stringify([refs.run("ab"), refs.run("xyz")]));';
        $output = json_decode((string)shell_exec('node -e ' . escapeshellarg($script) . ' 2>&1'), true);

        $php = function ($s) {
            $list = [3, 1, 2];
            sort($list);
            if (preg_match('/(a)(b)?(c)?/', $s, $m)) {
                return [$m, $list];
            }
            return [null, $list];
        };
        $this->assertSame([$php('ab'), $php('xyz')], $output);
    }
}
