<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage_Slot5(
    \HomePage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= 'Dynamic 1';
    return $_content;
   
}
