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

    public static function getFiles(HtmlNode $input): array
    {
        <<<'javascript'
            return Array.prototype.slice.call(input.files);
            javascript;
        return [];
    }

    public static function getDomList(array $nodes): array
    {
        <<<'javascript'
            return Array.prototype.slice.call(nodes);
            javascript;
        return [];
    }
}
