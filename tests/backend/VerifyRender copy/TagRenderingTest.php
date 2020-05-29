<?php

include_once '../www/core/PageEngine/PageEngine.php';

class TagRenderingTest extends BaseTest
{

    public function VerifySimpleComponent(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            __DIR__ . DIRECTORY_SEPARATOR . 'build',
            true
        );
        ob_start();
        $page->render(HomePage::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results' . DIRECTORY_SEPARATOR . 'home.html');
        $T->this($html)->equalsTo($expectetd);
    }

    public function VerifyTagsContent(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            __DIR__ . DIRECTORY_SEPARATOR . 'build',
            true
        );
        ob_start();
        $page->render(HomePage::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results' . DIRECTORY_SEPARATOR . 'home.html');
        $T->this($html)->equalsTo($expectetd.' ');
    }
}
