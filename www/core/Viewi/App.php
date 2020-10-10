<?php

namespace Viewi;

include 'PageEngine.php';

class App
{
    private static PageEngine $engine;

    public static array $config;

    public static function init(array $config)
    {
        self::$config = $config;
        self::$engine = new PageEngine(
            $config['SOURCE_DIR'],
            $config['SERVER_BUILD_DIR'],
            $config['PUBLIC_BUILD_DIR'],
            $config['DEV_MODE'],
            $config['RETURN_OUTPUT']
        );
    }

    public static function run(string $component)
    {
        return self::$engine->render($component);
    }
}
