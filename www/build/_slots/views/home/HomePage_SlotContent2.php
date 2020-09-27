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
        <p>
            ';
    foreach($_component->fruits as $fruit){
    
    $_content .= '<b>Fruit: ';
    $_content .= htmlentities($fruit);
    $_content .= ' </b>';
    }
    
    $_content .= '
            ';
    if($_component->count % 2 === 0){
    
    $_content .= '<i>';
    $_content .= htmlentities($_component->count);
    $_content .= ' is Odd</i>';
    } else {
    
    $_content .= '<span>';
    $_content .= htmlentities($_component->count);
    $_content .= ' is Even</span>';
    }
    
    $_content .= '
        </p>
    ';
    return $_content;
   
}
