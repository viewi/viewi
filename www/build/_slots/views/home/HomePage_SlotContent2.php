<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage_SlotContent2(
    \HomePage $_component,
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
    $_content .= '';
    $_content .= htmlentities($_component->title ? ' show' : '');
    $_content .= '';
    $_content .= htmlentities($_component->title ? ' lg' : '');
    $_content .= '">
            Another count: ';
    $_content .= htmlentities($_component->count);
    $_content .= '
            <button>Increment</button>
        </div>
        ';
    if($_component->count % 3 === 0){
    
    $_content .= '<div>
            One
        </div>';
    } else if ($_component->count % 3 === 1){
    
    $_content .= '<div>
            Two
        </div>';
    } else {
    
    $_content .= '<div>
            Three
        </div>';
    }
    
    $_content .= '
    ';
    return $_content;
   
}
