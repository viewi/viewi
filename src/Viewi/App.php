<?php

namespace Viewi;

use Viewi\DI\IContainer;
use Viewi\Routing\Router;
use Viewi\WebComponents\Response;

class App
{
    public static array $config;

    public static ?array $publicConfig = null;

    public static function init(array $config, ?array $publicConfig = null)
    {
        $config[PageEngine::PUBLIC_BUILD_DIR] ??= '/viewi-build';
        self::$config = $config;
        self::$publicConfig = $publicConfig;
    }

    public static function use(string $packageClass)
    {
        if (!isset(self::$config[PageEngine::INCLUDES])) {
            self::$config[PageEngine::INCLUDES] = [];
        }
        self::$config[PageEngine::INCLUDES][] = $packageClass;
    }

    public static function run(string $component, array $params, ?IContainer $container = null)
    {
        return self::getEngine()->render($component, $params, $container);
    }

    public static function getEngine(): PageEngine
    {
        return new PageEngine(self::$config, self::$publicConfig);
    }

    public static function handle(?string $url = null, string $method = null): void
    {
        $url ??= $_SERVER['REDIRECT_URL'] ?? preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        $method ??= $_SERVER['REQUEST_METHOD'];
        $response = Router::handle($url, $method, $_REQUEST);
        if (is_string($response)) { // html
            header("Content-type: text/html; charset=utf-8");
            echo $response;
        } else if ($response instanceof Response) {
            http_response_code($response->StatusCode);
            foreach ($response->Headers as $name => $value) {
                header("$name: $value");
            }
            if ($response->Stringify) {
                echo json_encode($response->Content);
            } else {
                echo $response->Content;
            }
        } else { // json
            header("Content-type: application/json; charset=utf-8");
            echo json_encode($response);
        }
    }
}
