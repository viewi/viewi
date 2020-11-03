<?php

namespace Viewi;

class App
{
    private static PageEngine $engine;

    public static array $config;

    public static function init(array $config)
    {
        self::$config = $config;
        self::$engine = new PageEngine(self::$config);
    }

    public static function run(string $component, array $params)
    {
        return self::$engine->render($component, $params);
    }

    public static function getEngine(): PageEngine
    {
        return self::$engine;
    }
}
