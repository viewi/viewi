<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage_Slot7(
    \HomePage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
    
    
    Just text without slot
';
    return $_content;
   
}
