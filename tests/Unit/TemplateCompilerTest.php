<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use Viewi\Builder\BuildItem;
use Viewi\JsTranspile\ExportItem;
use Viewi\JsTranspile\JsTranspiler;
use Viewi\TemplateCompiler\TemplateCompiler;
use Viewi\TemplateParser\TemplateParser;

class TemplateCompilerTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    protected TemplateCompiler $templateCompiler;
    protected TemplateParser $parser;
    protected JsTranspiler $transpiler;

    protected function _before()
    {
        $this->transpiler = new JsTranspiler();
        $this->parser = new TemplateParser();
        $this->templateCompiler = new TemplateCompiler($this->transpiler);
    }

    // tests
    public function testCanCompile()
    {
        $jsOutput = $this->transpiler->convert(
            <<<'php'
            <?php

            namespace Components\Views\Home;

            use Viewi\Components\BaseComponent;

            class HomePage extends BaseComponent
            {
                public string $title = 'Viewi v2 - Build reactive front-end with PHP';
            }
            php
        );
        $root = $this->parser->parse('<div>$title</div>');
        $buildItem = new BuildItem('HomePage', $jsOutput);
        $buildItem->publicNodes['title'] = ExportItem::Property;
        $renderItem = $this->templateCompiler->compile($root, $buildItem);
        $this->assertEquals('RenderHomePage', $renderItem->renderName);
        $this->assertEquals(false, $renderItem->hasHtmlTag);
        $this->assertCount(1, $renderItem->inlineExpressions);
        $inlineExpression = $renderItem->inlineExpressions[0];
        $this->assertCount(2, $inlineExpression);
        $this->assertEquals('_component.title', $inlineExpression[0]);
        $this->assertEquals(
            $this->normalizeString(
                <<<'php'
                function RenderHomePage(
                    Viewi\Engine $_engine,
                    \HomePage $_component,
                    array $_slots,
                    array $_scope
                ) {
                    $_content = '';
                    
                    $_content .= '<div>';
                    $_content .= htmlentities($_component->title ?? '');
                    $_content .= '</div>';
                    return $_content;
                }
                php
            ),
            $this->normalizeString($renderItem->renderCode)
        );
    }

    private function normalizeString(string $input): string
    {
        $output = trim(str_replace(["\n", "\r"], [PHP_EOL, ''], $input));
        return $output;
    }
}
