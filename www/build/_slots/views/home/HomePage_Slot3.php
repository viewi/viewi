<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage_Slot3(
    \HomePage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
    
    
    <div>';
    if($_component->count % 2 === 0){
    
    $_content .= '
            ==EVEN==
        ';
    }
    
    $_content .= '
        Just text without slot
        Test is: ';
    $_content .= htmlentities($_component->Test($_component->count));
    $_content .= '
        ';
    if($_component->count % 2 === 0){
    
    $_content .= '<span>
            ==ODD==
        </span>';
    } else {
    
    $_content .= '
            ==EVEN==
        ';
    }
    
    $_content .= '
        Second test is: ';
    $_content .= htmlentities($_component->Test($_component->count));
    $_content .= '
        Simple merge test ';
    $_content .= htmlentities($_component->title);
    $_content .= '
        ';
    if($_component->count % 2 === 0){
    
    $_content .= '
            ==EVEN==
        ';
    }
    
    $_content .= '</div>
';
    return $_content;
   
}
