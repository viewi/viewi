<?php

namespace Viewi\Components\Services;

use Viewi\DOM\Elements\HtmlNode;

class DomHelper
{
    public static function getDocument(): ?HtmlNode
    {
        <<<'javascript'
        return document;
        javascript;
        // nothing on server-side
        return null;
    }

    public static function getWindow(): ?HtmlNode
    {
        <<<'javascript'
        return window;
        javascript;
        // nothing on server-side
        return null;
    }
}
