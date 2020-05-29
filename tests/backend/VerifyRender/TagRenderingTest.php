<?php

include_once '../www/core/PageEngine/PageEngine.php';

class TagRenderingTest extends BaseTest
{

    public function VerifySimpleTagRendering(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render(HomeTest::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results'
            . DIRECTORY_SEPARATOR . 'homeTest.html');
        $T->this($html)->equalsTo($expectetd);
    }

    public function VerifyRawHtmlRendering(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render(RawHtmlComponent::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results'
            . DIRECTORY_SEPARATOR . 'RawHtmlComponent.html');
        $T->this($html)->equalsTo($expectetd);
    }

    public function VerifyComponentRendering(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render(ComponentTest::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results'
            . DIRECTORY_SEPARATOR . 'ComponentTest.html');
        $T->this($html)->equalsTo($expectetd);
    }

    public function VerifyComponentSlotRendering(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render(ChildComponentSlot::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results'
            . DIRECTORY_SEPARATOR . 'ChildComponentSlot.html');
        $T->this($html)->equalsTo($expectetd);
    }

    public function VerifyComponentSlotDefaultRendering(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render(ChildComponentSlotDefault::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results'
            . DIRECTORY_SEPARATOR . 'ChildComponentSlotDefault.html');
        $T->this($html)->equalsTo($expectetd);
    }
}
