<?php

include_once '../www/core/PageEngine/PageEngine.php';

class RenderingTestV2 extends BaseTest
{

    public function VerifyHomePage(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render(HomePageV2::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results' . DIRECTORY_SEPARATOR . 'home.html');
        $T->this($html)->equalsTo($expectetd);
    }

    public function VerifyTagsContent(UnitTestScope $T)
    {
        $page = new PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . 'app',
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->render(HomePageV2::class);
        $html = ob_get_contents();
        ob_end_clean();
        $expectetd = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'results' . DIRECTORY_SEPARATOR . 'home.html');
        $T->this($html)->equalsTo($expectetd);
    }
}
