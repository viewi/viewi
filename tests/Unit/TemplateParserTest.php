<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use Viewi\TemplateParser\TagItemType;
use Viewi\TemplateParser\TemplateParser;

class TemplateParserTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
    }

    // tests
    public function testSomeFeature()
    {
        $parser = new TemplateParser();
        $root = $parser->parse('<div>Hello World!</div>');
        $this->assertCount(1, $root->getChildren());
        $div = $root->currentChild();
        $this->assertEquals(TagItemType::Tag, $div->Type->Name);
        $this->assertEquals('div', $div->Content);
        $this->assertCount(1, $div->getChildren());
        $text = $div->currentChild();
        $this->assertEquals(TagItemType::TextContent, $text->Type->Name);
        $this->assertEquals('Hello World!', $text->Content);
    }
}
