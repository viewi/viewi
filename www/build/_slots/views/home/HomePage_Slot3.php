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
    
    
    Just text without slot
    Test is: ';
    $_content .= htmlentities($_component->Test($_component->count));
    $_content .= '
    Second test is: ';
    $_content .= htmlentities($_component->Test($_component->count));
    $_content .= '
    Simple merge test ';
    $_content .= htmlentities($_component->title);
    $_content .= '
';
    return $_content;
   
}
