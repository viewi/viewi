<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

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
';
    return $_content;
   
}
