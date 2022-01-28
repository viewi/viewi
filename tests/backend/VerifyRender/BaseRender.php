<?php

use Viewi\App;
use Viewi\BaseComponent;
use Viewi\PageEngine;
use Viewi\Routing\Router;
use Viewi\WebComponents\Response;

include_once '../src/Viewi/autoload.php';

class BaseRenderingTest extends BaseTest
{
    protected bool $returnRendering = false;
    protected RenderTestCases $TestCases;
    function __construct()
    {
        $this->TestCases = new RenderTestCases();
        $this->TestCases->CanRenderTag = [
            'path' => 'CanRenderTag',
            'class' => CanRenderTagComponent::class,
            'expected' => 'CanRenderTag.expected.html'
        ];
        $this->TestCases->CanRenderRawHtml = [
            'path' => 'CanRenderRawHtml',
            'class' => RawHtmlComponent::class,
            'expected' => 'RawHtmlComponent.expected.html'
        ];
        $this->TestCases->CanRenderComponentTag = [
            'path' => 'CanRenderComponentTag',
            'class' => CanRenderComponentTagComponent::class,
            'expected' => 'CanRenderComponentTag.expected.html'
        ];
        $this->TestCases->CanRenderSlots = [
            'path' => 'CanRenderSlots',
            'class' => CanRenderSlotsComponent::class,
            'expected' => 'CanRenderSlots.expected.html'
        ];
        $this->TestCases->CanRenderAttributes = [
            'path' => 'CanRenderAttributes',
            'class' => CanRenderAttributesComponent::class,
            'expected' => 'CanRenderAttributes.expected.html'
        ];
        $this->TestCases->CanRenderDynamicTag = [
            'path' => 'CanRenderDynamicTag',
            'class' => DynTestAppComponent::class,
            'expected' => 'CanRenderDynamicTag.expected.html'
        ];
        $this->TestCases->CanRenderNamedSlots = [
            'path' => 'CanRenderNamedSlots',
            'class' => NamedSlotsAppComponent::class,
            'expected' => 'CanRenderNamedSlots.expected.html'
        ];
        $this->TestCases->CanPassAttributesAsComponentInputs = [
            'path' => 'CanPassAttributesAsComponentInputs',
            'class' => CanPassAttributesAsComponentInputsComponent::class,
            'expected' => 'CanPassAttributesAsComponentInputs.expected.html'
        ];
        $this->TestCases->CanRenderConditionalAttributes = [
            'path' => 'CanRenderConditionalAttributes',
            'class' => ConditionalAttributesComponent::class,
            'expected' => 'CanRenderConditionalAttributes.expected.html'
        ];
        $this->TestCases->ConditionalAndForeachRendering = [
            'path' => 'ConditionalAndForeachRendering',
            'class' => ConditionalAndForeachRenderingComponent::class,
            'expected' => 'ConditionalAndForeachRendering.expected.html'
        ];
        $this->TestCases->CanRenderTemplates = [
            'path' => 'CanRenderTemplates',
            'class' => CanRenderTemplatesComponent::class,
            'expected' => 'CanRenderTemplates.expected.html'
        ];
        $this->TestCases->EscapingAndExpressions = [
            'path' => 'EscapingAndExpressions',
            'class' => EscapingAndExpressionsComponent::class,
            'expected' => 'EscapingAndExpressions.expected.html'
        ];
        $this->TestCases->ComplexTest = [
            'path' => 'ComplexTest',
            'class' => ComplexTestComponent::class,
            'expected' => 'ComplexTest.expected.html'
        ];
        $this->TestCases->DependencyInjectionWorks = [
            'path' => 'DependencyInjectionWorks',
            'class' => DependencyInjectionWorksComponent::class,
            'expected' => 'DependencyInjectionWorks.expected.html'
        ];
        $this->TestCases->HelloWorldExample = [
            'path' => 'HelloWorld',
            'class' => HelloWorldComponent::class,
            'expected' => 'HelloWorld.expected.html'
        ];
        $this->TestCases->TestInterceptors = [
            'path' => 'TestInterceptors',
            'class' => TestInterceptorsComponent::class,
            'expected' => 'TestInterceptors.expected.html'
        ];
        $this->TestCases->TestInterceptorsUnauthorized = [
            'path' => 'TestInterceptorsUnauthorized',
            'class' => TestInterceptorsComponent::class,
            'expected' => 'TestInterceptorsUnauthorized.expected.html'
        ];
        $this->TestCases->TestMiddleware = [
            'path' => 'TestMiddleware',
            'class' => TestMiddlewareComponent::class,
            'expected' => 'TestMiddleware.expected.html'
        ];
        $this->TestCases->TestMiddlewareUnauthorized = [
            'path' => 'TestMiddlewareUnauthorized',
            'class' => TestMiddlewareComponent::class,
            'expected' => 'TestMiddleware.expected.html'
        ];
        $this->TestCases->TestHttpClient = [
            'path' => 'TestHttpClient',
            'class' => TestHttpClientComponent::class,
            'expected' => 'TestHttpClient.expected.html'
        ];
    }
    protected function TestComponent(
        string $component,
        string $path,
        string $expectedResultFile,
        UnitTestScope $T,
        bool $echoRendered = false,
        bool $async = false
    ) {
        App::init([
            PageEngine::SOURCE_DIR => __DIR__ . DIRECTORY_SEPARATOR . $path,
            PageEngine::SERVER_BUILD_DIR => $T->WorkingDirectory(),
            PageEngine::PUBLIC_ROOT_DIR => $T->WorkingDirectory(),
            PageEngine::DEV_MODE => true,
            PageEngine::RETURN_OUTPUT => $this->returnRendering
        ]);
        $page = App::getEngine();
        $html = null;
        if ($async) {
            $page->render($component, [], null, function ($content) use (&$html) {
                $html = $content;
            });
        } else {
            if ($this->returnRendering) {
                $html = $page->render($component);
            } else {
                ob_start();
                $page->render($component);
                $html = ob_get_contents();
                ob_end_clean();
            }
        }
        $expected = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $path
            . DIRECTORY_SEPARATOR . $expectedResultFile);
        if ($echoRendered) {
            var_dump($html);
            var_dump($expected);

            $r = str_split($html);
            $e = str_split($expected);
            for ($i = 0; $i < min(count($r), count($e)); $i++) {
                if ($r[$i] !== $e[$i]) {
                    var_dump($i);
                    for ($k = 10; $k > 0; $k--) {
                        var_dump([$r[$i - $k], $e[$i - $k]]);
                    }
                    var_dump([$r[$i], $e[$i]]);
                    break;
                }
            }
        }
        try {
            $T->this($html)->equalsToHtml($expected);
        } catch (Throwable $error) {
            var_dump($html);
            var_dump($expected);
            throw $error;
        }
    }

    protected function TestAppInFolder(array $testCase, UnitTestScope $T, bool $echoResult = false, bool $async = false)
    {
        $this->TestComponent($testCase['class'], $testCase['path'], $testCase['expected'], $T, $echoResult, $async);
    }

    public function CanRenderTag(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderTag, $T);
    }

    public function CanRenderRawHtml(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderRawHtml, $T);
    }

    public function CanRenderComponentTag(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderComponentTag, $T);
    }

    public function CanRenderSlots(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderSlots, $T);
    }

    public function CanRenderAttributes(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderAttributes, $T);
    }

    public function CanRenderDynamicTag(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderDynamicTag, $T);
    }

    public function CanRenderNamedSlots(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderNamedSlots, $T);
    }

    public function CanPassAttributesAsComponentInputs(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanPassAttributesAsComponentInputs, $T);
    }

    public function CanRenderConditionalAttributes(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderConditionalAttributes, $T);
    }

    public function ConditionalAndForeachRendering(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->ConditionalAndForeachRendering, $T);
    }

    public function CanRenderTemplates(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->CanRenderTemplates, $T);
    }

    public function EscapingAndExpressions(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->EscapingAndExpressions, $T);
    }

    public function ComplexTest(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->ComplexTest, $T);
    }

    public function DependencyInjectionWorks(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->DependencyInjectionWorks, $T);
    }

    public function HelloWorldTest(UnitTestScope $T)
    {
        $this->TestAppInFolder($this->TestCases->HelloWorldExample, $T);
    }
    // php test.php run backend\\VerifyRender\\ReturnRender.test.php ReturnRenderingTest HttpClientTest
    public function HttpClientTest(UnitTestScope $T)
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . $this->TestCases->TestHttpClient['path'] . DIRECTORY_SEPARATOR . 'PostModel.php';
        Router::register('get', '/api/posts/{id}', function (int $id) {
            $post = new TestHttpClient\PostModel();
            $post->Id = $id;
            $post->Title = 'Testing HttpClient';
            $post->Body = '<p>My post content</p><div>Something interesting</div>';
            return $post;
        });
        $this->TestAppInFolder($this->TestCases->TestHttpClient, $T);
    }

    // php test.php run backend\\VerifyRender\\ReturnRender.test.php ReturnRenderingTest InterceptorsTest
    public function InterceptorsTest(UnitTestScope $T)
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
        $this->TestAppInFolder($this->TestCases->TestInterceptors, $T);
    }

    // php test.php run backend\\VerifyRender\\ReturnRender.test.php ReturnRenderingTest InterceptorsUnauthorizedTest
    public function InterceptorsUnauthorizedTest(UnitTestScope $T)
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . $this->TestCases->TestInterceptorsUnauthorized['path'] . DIRECTORY_SEPARATOR . 'PostModel.php';
        Router::register('get', '/api/posts/{id}', function (int $id) {
            $post = new TestInterceptors\PostModel();
            $post->Id = $id;
            $post->Title = 'Testing Interceptors';
            $post->Body = '<p>My post content</p><div>Something interesting</div>';
            return $post;
        });
        Router::register('post', '/api/authorization/token/{valid}', function (int $valid) {
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
        $this->TestAppInFolder($this->TestCases->TestInterceptorsUnauthorized, $T);
    }

    // php test.php run backend\\VerifyRender\\ReturnRender.test.php ReturnRenderingTest MiddlewareTest
    public function MiddlewareTest(UnitTestScope $T)
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . $this->TestCases->TestMiddleware['path'] . DIRECTORY_SEPARATOR . 'PostModel.php';
        Router::register('get', '/api/posts/{id}', function (int $id) {
            $post = new TestMiddleware\PostModel();
            $post->Id = $id;
            $post->Title = 'Testing Middleware';
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
        $this->TestAppInFolder($this->TestCases->TestMiddleware, $T);
    }

    // php test.php run backend\\VerifyRender\\ReturnRender.test.php ReturnRenderingTest MiddlewareUnauthorizedTest
    public function MiddlewareUnauthorizedTest(UnitTestScope $T)
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . $this->TestCases->TestMiddlewareUnauthorized['path'] . DIRECTORY_SEPARATOR . 'PostModel.php';
        Router::register('get', '/api/posts/{id}', function (int $id) {
            $post = new TestMiddleware\PostModel();
            $post->Id = $id;
            $post->Title = 'Testing Middleware';
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
        $this->TestAppInFolder($this->TestCases->TestMiddlewareUnauthorized, $T);
    }
}

class RenderTestCases
{
    public array $CanRenderTag;
    public array $CanRenderRawHtml;
    public array $CanRenderComponentTag;
    public array $CanRenderSlots;
    public array $CanRenderAttributes;
    public array $CanRenderDynamicTag;
    public array $CanRenderNamedSlots;
    public array $CanPassAttributesAsComponentInputs;
    public array $CanRenderConditionalAttributes;
    public array $ConditionalAndForeachRendering;
    public array $CanRenderTemplates;
    public array $EscapingAndExpressions;
    public array $ComplexTest;
    public array $DependencyInjectionWorks;
    public array $HelloWorldExample;
    public array $TestHttpClient;
    public array $TestInterceptors;
    public array $TestInterceptorsUnauthorized;
    public array $TestMiddleware;
    public array $TestMiddlewareUnauthorized;
}
