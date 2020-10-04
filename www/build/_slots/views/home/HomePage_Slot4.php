<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage_Slot4(
    \HomePage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= 'DYNAMIC TAG
        ';
    return $_content;
   
}
