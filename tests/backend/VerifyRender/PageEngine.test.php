<?php

include_once '../www/core/PageEngine/PageEngine.php';

class TagRenderingTest extends BaseTest
{
    private function TestComponent(string $component, string $path, string $expectedResultFile, UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . $path,
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render($component);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $path
            . DIRECTORY_SEPARATOR . $expectedResultFile);
        $T->this($html)->equalsTo($expectetd);
    }

    public function CanRenderTag(UnitTestScope $T)
    {
        $this->TestComponent(CanRenderTagComponent::class, 'CanRenderTag', 'CanRenderTag.expected.html', $T);
    }

    public function CanRenderRawHtml(UnitTestScope $T)
    {
        $this->TestComponent(RawHtmlComponent::class, 'CanRenderRawHtml', 'RawHtmlComponent.expected.html', $T);
    }

    public function CanRenderComponentTag(UnitTestScope $T)
    {
        $this->TestComponent(CanRenderComponentTagComponent::class, 'CanRenderComponentTag', 'CanRenderComponentTag.expected.html', $T);
    }

    public function CanRenderSlots(UnitTestScope $T)
    {
        $this->TestComponent(CanRenderSlotsComponent::class, 'CanRenderSlots', 'CanRenderSlots.expected.html', $T);
    }

    // public function VerifyComponentSlotDefaultRendering(UnitTestScope $T)
    // {
    //     $this->TestComponent(ChildComponentSlotDefault::class, 'app', 'expected.html', $T);
    // }
}
