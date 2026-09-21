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

    /** document.getElementById - for elements outside the component's refs (an Overlay's content). */
    public static function getElementById(string $id): ?HtmlNode
    {
        <<<'javascript'
        return document.getElementById(id);
        javascript;
        // nothing on server-side
        return null;
    }

    /** The focused element, to check whether a focus() call landed. */
    public static function getActiveElement(): ?HtmlNode
    {
        <<<'javascript'
        return document.activeElement;
        javascript;
        // nothing on server-side
        return null;
    }

    /** Run $action before the next repaint - after the pending render has reached the DOM. */
    public static function requestAnimationFrame(callable $action): int
    {
        <<<'javascript'
        return window.requestAnimationFrame(action);
        javascript;
        // nothing on server-side
        return 0;
    }
}
