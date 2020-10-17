<?php

use Viewi\BaseComponent;

include_once 'BaseRender.php';

class EchoRenderingTest extends BaseRenderingTest
{
    public function EchoRenderPerformance(UnitTestScope $T)
    {
        $component = ComplexTestComponent::class;
        $path = 'PerformanceTest';
        $startedAt = microtime(true);
        $page = new Viewi\PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . $path,
            $T->WorkingDirectory(),
            $T->WorkingDirectory(),
            true
        );
        ob_start();
        $page->compile();
        $compileTime = floor((microtime(true) - $startedAt) * 1000);

        $startedAt = microtime(true);
        $page = new Viewi\PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . $path,
            $T->WorkingDirectory(),
            $T->WorkingDirectory(),
            false
        );
        $howMany = 500;
        for ($i = 0; $i < $howMany; $i++) {
            $page->render($component);
        }
        $html = ob_get_contents();
        ob_end_clean();
        $time = floor((microtime(true) - $startedAt) * 1000);
        $T->this($html)->isNotEmpty();
        echo "   Compile time: \033[44;100m{$compileTime}ms\033[0m\n";
        $perOne = round($time / $howMany, 4);
        $perSec = number_format(floor(1000 / $perOne), 2, '.', ' ');
        echo "   Run $howMany times: \033[44;100m{$time}ms;\033[0m \033[44;100m{$perOne}ms/render; $perSec rps (renders/sec);\033[0m\n";
        $T->this($time)->lessThan(200);
    }
}
