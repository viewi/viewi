<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage_SlotContent2(
    \HomePage $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        <h1>';
    $_content .= htmlentities($component->title);
    $_content .= '</h1>
        <h4>
            Count: ';
    $_content .= htmlentities($component->count);
    $_content .= '
        </h4>
        <div>
            Another count: ';
    $_content .= htmlentities($component->count);
    $_content .= ' <button (click)="Increment()">Increment</button>
        </div>
    ';
    return $_content;
   
}
