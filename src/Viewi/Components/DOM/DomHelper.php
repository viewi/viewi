<?php

namespace Viewi\Components\DOM;

use Viewi\DI\Singleton;

#[Singleton]
class DomHelper
{
    public function getDocument(): ?HtmlNode
    {
        <<<'javascript'
        return document;
        javascript;
        // nothing on server-side
        return null;
    }

    public function getWindow(): ?HtmlNode
    {
        <<<'javascript'
        return window;
        javascript;
        // nothing on server-side
        return null;
    }
}
