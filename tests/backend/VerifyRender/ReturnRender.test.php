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

    private function PerfrmanceTest(UnitTestScope $T, $component, $path, $iterations = 500)
    {

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
        $howMany = $iterations;
        for ($i = 0; $i < $howMany; $i++) {
            $html = $page->render($component);
        }
        $time = floor((microtime(true) - $startedAt) * 1000);
        $T->this($html)->isNotEmpty();
        echo "   Compile time: \033[44;100m{$compileTime}ms\033[0m\n";
        $perOne = round($time / $howMany, 6);
        $perSec = number_format(floor(1000 / $perOne), 2, '.', ' ');
        echo "   Run $howMany times: \033[44;100m{$time}ms;\033[0m \033[44;100m{$perOne}ms/render; $perSec rps (renders/sec);\033[0m\n";
        $T->this($time)->lessThan(200);
    }

    public function ReturnRenderComplexPerformance(UnitTestScope $T)
    {
        $component = ComplexTestComponent::class;
        $path = 'PerformanceTest';
        $this->PerfrmanceTest($T, $component, $path);
    }

    public function ReturnRenderHelloWorldPerformance(UnitTestScope $T)
    {
        $component = HelloWorldComponent::class;
        $path = 'HelloWorld';
        $this->PerfrmanceTest($T, $component, $path, 1000);
    }
}
