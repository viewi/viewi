<?php

use Viewi\App;
use Viewi\BaseComponent;
use Viewi\PageEngine;
use Viewi\Routing\Router;
use Viewi\WebComponents\Response;

include_once 'BaseRender.php';

class ReturnRenderingTest extends BaseRenderingTest
{
    function __construct()
    {
        parent::__construct();
        $this->returnRendering = true;
    }

    private function PerformanceTest(UnitTestScope $T, $component, $path, $iterations = 500)
    {

        $startedAt = microtime(true);
        App::init([
            PageEngine::SOURCE_DIR => __DIR__ . DIRECTORY_SEPARATOR . $path,
            PageEngine::SERVER_BUILD_DIR => $T->WorkingDirectory(),
            PageEngine::PUBLIC_ROOT_DIR => $T->WorkingDirectory(),
            PageEngine::DEV_MODE => true,
            PageEngine::RETURN_OUTPUT => true
        ]);
        $page = App::getEngine();
        $page->compile();
        $compileTime = floor((microtime(true) - $startedAt) * 1000);

        $startedAt = microtime(true);
        App::init([
            PageEngine::SOURCE_DIR => __DIR__ . DIRECTORY_SEPARATOR . $path,
            PageEngine::SERVER_BUILD_DIR => $T->WorkingDirectory(),
            PageEngine::PUBLIC_ROOT_DIR => $T->WorkingDirectory(),
            PageEngine::DEV_MODE => false,
            PageEngine::RETURN_OUTPUT => true
        ]);
        $page = App::getEngine();
        $html = '';
        $howMany = $iterations;
        for ($i = 0; $i < $howMany; $i++) {
            $html = $page->render($component);
        }
        $precision = 1000;
        $scaledIterationsNum = $howMany * $precision;
        $time = floor((microtime(true) - $startedAt) * 1000 * $precision);
        $realTime = $time / $precision;
        $T->this($html)->isNotEmpty();
        echo "   Compile time: \033[44;100m{$compileTime}ms\033[0m\n";
        $perOne = round($time / $scaledIterationsNum, 6);
        $perSec = number_format(floor(1000 / $perOne), 2, '.', ' ');
        echo "   Run $howMany times: \033[44;100m{$realTime}ms;\033[0m \033[44;100m{$perOne}ms/render; $perSec rps (renders/sec);\033[0m\n";
        $T->this($realTime)->lessThan(200);
    }

    public function ReturnRenderComplexPerformance(UnitTestScope $T)
    {
        $component = ComplexTestComponent::class;
        $path = 'PerformanceTest';
        $this->PerformanceTest($T, $component, $path);
    }

    // php test.php run backend\\VerifyRender\\ReturnRender.test.php ReturnRenderingTest ReturnRenderHelloWorldPerformance
    public function ReturnRenderHelloWorldPerformance(UnitTestScope $T)
    {
        $component = HelloWorldComponent::class;
        $path = 'HelloWorld';
        $this->PerformanceTest($T, $component, $path, 1000);
    }

    public function AsyncRenderTest(UnitTestScope $T)
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . $this->TestCases->TestInterceptors['path'] . DIRECTORY_SEPARATOR . 'PostModel.php';
        Router::register('get', '/api/posts/{id}', function (int $id) {
            $post = new TestInterceptors\PostModel();
            $post->Id = $id;
            $post->Title = 'Testing Interceptors';
            $post->Body = '<p>My post content</p><div>Something interesting</div>';
            return $post;
        });
        Router::register('post', '/api/authorization/token/{valid}', function (bool $valid) {
            if (!$valid) {
                $response = new Response();
                $response->Content = '';
                $response->WithCode(401);
                return $response;
            }
            return ['token' => 'base64string'];
        });
        Router::register('post', '/api/authorization/session', function () {
            return ['session' => '000-1111-2222'];
        });
        $this->TestAppInFolder($this->TestCases->TestInterceptors, $T, false, true);
    }
}
