<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage_Slot16(
    \HomePage $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= 'DefaultContent ';
    $_content .= htmlentities($component->title);
    $_content .= ' ';
    return $_content;
   
}
