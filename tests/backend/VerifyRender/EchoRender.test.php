<?php

use Viewi\App;
use Viewi\BaseComponent;
use Viewi\PageEngine;

include_once 'BaseRender.php';

class EchoRenderingTest extends BaseRenderingTest
{
    public function EchoRenderPerformance(UnitTestScope $T)
    {
        $component = ComplexTestComponent::class;
        $path = 'PerformanceTest';
        $startedAt = microtime(true);
        App::init([
            PageEngine::SOURCE_DIR => __DIR__ . DIRECTORY_SEPARATOR . $path,
            PageEngine::SERVER_BUILD_DIR => $T->WorkingDirectory(),
            PageEngine::PUBLIC_ROOT_DIR => $T->WorkingDirectory(),
            PageEngine::DEV_MODE => true,
            PageEngine::RETURN_OUTPUT => false
        ]);
        $page = App::getEngine();
        ob_start();
        $page->compile();
        $compileTime = floor((microtime(true) - $startedAt) * 1000);

        $startedAt = microtime(true);
        App::init([
            PageEngine::SOURCE_DIR => __DIR__ . DIRECTORY_SEPARATOR . $path,
            PageEngine::SERVER_BUILD_DIR => $T->WorkingDirectory(),
            PageEngine::PUBLIC_ROOT_DIR => $T->WorkingDirectory(),
            PageEngine::DEV_MODE => false,
            PageEngine::RETURN_OUTPUT => false
        ]);
        $page = App::getEngine();
        $howMany = 500;
        for ($i = 0; $i < $howMany; $i++) {
            $page->render($component);
        }
        $html = ob_get_contents();
        ob_end_clean();
        $precision = 1000;
        $scaledIterationsNum = $howMany * $precision;
        $time = floor((microtime(true) - $startedAt) * 1000 * $precision);
        $realTime = $time / $precision;
        $T->this($html)->isNotEmpty();
        echo "   Compile time: \033[44;100m{$compileTime}ms\033[0m\n";
        $perOne = round($time / $scaledIterationsNum, 4);
        $perSec = number_format(floor(1000 / $perOne), 2, '.', ' ');
        echo "   Run $howMany times: \033[44;100m{$realTime}ms;\033[0m \033[44;100m{$perOne}ms/render; $perSec rps (renders/sec);\033[0m\n";
        $T->this($realTime)->lessThan(200);
    }
}
