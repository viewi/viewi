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
    $_content .= htmlentities($_component->title);
    $_content .= '</h1>
        <h4>
            Count: ';
    $_content .= htmlentities($_component->count);
    $_content .= '
        </h4>
        <div class="my-class count-';
    $_content .= htmlentities($_component->count);
    $_content .= '">
            Another count: ';
    $_content .= htmlentities($_component->count);
    $_content .= ' 
            <button>Increment</button>
        </div>
    ';
    return $_content;
   
}
