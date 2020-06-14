<?php

use Vo\BaseComponent;

include_once 'BaseRender.php';
include_once '../www/core/PageEngine/PageEngine.php';

class ReturnRenderingTest extends BaseRenderingTest
{
    function __construct()
    {
        parent::__construct();
        $this->returnRendering = true;
    }

    public function ReturnRenderPerformance(UnitTestScope $T)
    {
        $component = ComplexTestComponent::class;
        $path = 'PerformanceTest';
        $startedAt = microtime(true);
        $page = new Vo\PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . $path,
            $T->WorkingDirectory(),
            $T->WorkingDirectory(),
            true,
            true
        );
        $page->Compile();
        $compileTime = floor((microtime(true) - $startedAt) * 1000);

        $startedAt = microtime(true);
        $page = new Vo\PageEngine(
            __DIR__ . DIRECTORY_SEPARATOR . $path,
            $T->WorkingDirectory(),
            $T->WorkingDirectory(),
            false,
            true
        );
        $html = '';
        $howMany = 500;
        for ($i = 0; $i < $howMany; $i++) {
            $html .= $page->render($component);
        }
        $T->this($html)->isNotEmpty();
        $time = floor((microtime(true) - $startedAt) * 1000);
        echo "   Compile time: \033[44;100m{$compileTime}ms\033[0m\n";
        $perOne = round($time / $howMany, 4);
        $perSec = number_format(floor(1000 / $perOne), 2, '.', ' ');
        echo "   Run $howMany times: \033[44;100m{$time}ms;\033[0m \033[44;100m{$perOne}ms/render; $perSec rps (renders/sec);\033[0m\n";
        $T->this($time)->lessThan(200);
    }
}
