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

    $_content .= 'SOME TEXT';
    return $_content;
   
}
