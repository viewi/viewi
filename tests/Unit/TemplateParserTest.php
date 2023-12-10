<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use Viewi\TemplateParser\TagItem;
use Viewi\TemplateParser\TagItemType;
use Viewi\TemplateParser\TemplateParser;

class TemplateParserTest extends \Codeception\Test\Unit
{
    const UNDEFINED = ['___UNDEFINED___'];
    protected UnitTester $tester;
    protected TemplateParser $parser;

    protected function _before()
    {
        $this->parser = new TemplateParser();
    }

    // tests
    public function testHelloWorld()
    {
        $root = $this->parser->parse('<div>Hello World!</div>');
        $this->assertCount(1, $root->getChildren());
        $div = $root->currentChild();
        $this->assertEquals(TagItemType::Tag, $div->Type->Name);
        $this->assertEquals('div', $div->Content);
        $this->assertCount(1, $div->getChildren());
        $text = $div->currentChild();
        $this->assertEquals(TagItemType::TextContent, $text->Type->Name);
        $this->assertEquals('Hello World!', $text->Content);
    }

    public function testSlot()
    {
        $root = $this->parser->parse(
            <<<'html'
            <div>
                <slot></slot>
            </div>
            html
        );
        $this->assertCount(1, $root->getChildren());
        $div = $root->currentChild();
        $this->assertEquals(TagItemType::Tag, $div->Type->Name);
        $this->assertEquals('div', $div->Content);
        $children = $div->getChildren();
        $this->assertCount(3, $children);
        $text = $div->currentChild();
        $this->assertEquals(TagItemType::TextContent, $text->Type->Name);
        $this->assertEquals("\n", $text->Content);
        $slot = $children[1];
        $this->assertEquals(TagItemType::Tag, $slot->Type->Name);
        $this->assertEquals("slot", $slot->Content);
    }

    public function testAttributes()
    {
        $root = $this->parser->parse(
            <<<'html'
            <div id="myid" class="$class" $data="true" $any="{getAny()}" combined="Hello $world"></div>
            html
        );
        $this->assertCount(1, $root->getChildren());
        $div = $root->currentChild();
        $this->assertTagItem(
            $div,
            TagItemType::Tag,
            'div',
            5,
            false,
            [
                [TagItemType::Attribute, 'id', 1, false, [[TagItemType::AttributeValue, 'myid', 0, false]]],
                [TagItemType::Attribute, 'class', 1, false, [[TagItemType::AttributeValue, '$class', 0, true]]],
                [TagItemType::Attribute, '$data', 1, true, [[TagItemType::AttributeValue, 'true', 0, false]]],
                [TagItemType::Attribute, '$any', 1, true, [[TagItemType::AttributeValue, 'getAny()', 0, true]]],
                [TagItemType::Attribute, 'combined', 2, false, [[TagItemType::AttributeValue, 'Hello ', 0, false], [TagItemType::AttributeValue, '$world', 0, true]]],
            ]
        );
    }

    public function testRaw()
    {
        $root = $this->parser->parse(
            <<<'html'
            <div>{{$rawHtml}} {{getHtml()}}</div>
            html
        );
        $this->assertCount(1, $root->getChildren());
        $div = $root->currentChild();
        $this->assertTagItem(
            $div,
            TagItemType::Tag,
            'div',
            3,
            false,
            [
                [TagItemType::TextContent, '{$rawHtml}', 0, true],
                [TagItemType::TextContent, ' ', 0, false],
                [TagItemType::TextContent, '{getHtml()}', 0, true]
            ]
        );
    }

    public function testComponent()
    {
        $this->parser->setAvaliableComponents(['MyComponent' => 1]);
        $root = $this->parser->parse(
            <<<'html'
            <MyComponent id="my-id" />
            html
        );
        $this->assertCount(1, $root->getChildren());
        $component = $root->currentChild();
        $this->assertTagItem(
            $component,
            TagItemType::Component,
            'MyComponent',
            1,
            false,
            [
                [TagItemType::Attribute, 'id', 1, false, [[TagItemType::AttributeValue, 'my-id', 0, false]]]
            ]
        );
    }

    public function testComponentFail()
    {
        $this->expectExceptionMessage("Component `MyComponent` not found.");
        $root = $this->parser->parse(
            <<<'html'
            <MyComponent id="my-id" />
            html
        );
        $this->assertCount(1, $root->getChildren());
        $component = $root->currentChild();
        $this->assertTagItem(
            $component,
            TagItemType::Component,
            'MyComponent',
            1,
            false,
            [
                [TagItemType::Attribute, 'id', 1, false, [[TagItemType::AttributeValue, 'my-id', 0, false]]]
            ]
        );
    }

    public function testVoid()
    {
        $root = $this->parser->parse(
            <<<'html'
            <area><base><br><col><embed><hr><img><input><link><meta><param><source><track><wbr>
            html
        );
        $this->assertCount(14, $root->getChildren());
        $this->assertTagItem(
            $root,
            TagItemType::Root,
            null,
            14,
            false,
            [
                [TagItemType::Tag, 'area', 0, false],
                [TagItemType::Tag, 'base', 0, false],
                [TagItemType::Tag, 'br', 0, false],
                [TagItemType::Tag, 'col', 0, false],
                [TagItemType::Tag, 'embed', 0, false],
                [TagItemType::Tag, 'hr', 0, false],
                [TagItemType::Tag, 'img', 0, false],
                [TagItemType::Tag, 'input', 0, false],
                [TagItemType::Tag, 'link', 0, false],
                [TagItemType::Tag, 'meta', 0, false],
                [TagItemType::Tag, 'param', 0, false],
                [TagItemType::Tag, 'source', 0, false],
                [TagItemType::Tag, 'track', 0, false],
                [TagItemType::Tag, 'wbr', 0, false],
            ]
        );
    }

    protected function assertTagItem(TagItem $tagItem, string $type, $content = self::UNDEFINED, ?int $kidsCount = null, ?bool $itsExpression = null, ?array $childrenToTest = null)
    {
        $this->assertEquals($type, $tagItem->Type->Name);
        if ($content !== self::UNDEFINED) {
            $this->assertEquals($content, $tagItem->Content);
        }
        if ($itsExpression !== null) {
            $this->assertEquals($itsExpression, $tagItem->ItsExpression);
        }
        if ($kidsCount !== null || $childrenToTest !== null) {
            $children = $tagItem->getChildren();
            $totalChildren = count($children);
            if ($kidsCount !== null) {
                $this->assertCount($kidsCount, $children);
            }
            if ($childrenToTest !== null) {
                foreach ($childrenToTest as $i => $child) {
                    if ($child !== null) {
                        if ($i >= $totalChildren) {
                            $this->fail("Chidlren count does not match the expected number. Count: $totalChildren. Fetching $i.");
                        }
                        $childTagItem = $children[$i];
                        $childType = $child[0];
                        $childContent = $child[1] ?? self::UNDEFINED;
                        $childCount = $child[2] ?? null;
                        $childExpression = $child[3] ?? null;
                        $childChildren = $child[4] ?? null;
                        $this->assertTagItem($childTagItem, $childType, $childContent, $childCount, $childExpression, $childChildren);
                    }
                }
            }
        }
    }
}
