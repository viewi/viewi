<?php

include_once '../www/core/PageEngine/PageEngine.php';

class TagRenderingTest extends BaseTest
{
    private function TestComponent(string $component, UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render($component);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results'
            . DIRECTORY_SEPARATOR . $component . '.html');
        $T->this($html)->equalsTo($expectetd);
    }

    public function VerifySimpleTagRendering(UnitTestScope $T)
    {
        $this->TestComponent(HomeTest::class, $T);
    }

    public function VerifyRawHtmlRendering(UnitTestScope $T)
    {
        $this->TestComponent(RawHtmlComponent::class, $T);
    }

    public function VerifyComponentRendering(UnitTestScope $T)
    {
        $this->TestComponent(ComponentTest::class, $T);
    }

    public function VerifyComponentSlotRendering(UnitTestScope $T)
    {
        $this->TestComponent(ChildComponentSlot::class, $T);
    }

    public function VerifyComponentSlotDefaultRendering(UnitTestScope $T)
    {
        $this->TestComponent(ChildComponentSlotDefault::class, $T);
    }
}
