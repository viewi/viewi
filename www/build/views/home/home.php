<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage(
    \HomePage $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= 'Recursion ';
    $_content .= htmlentities($component->title);
    $_content .= '
';
    $slotContents[0] = 'HomePage_Slot16';
    $_content .= $pageEngine->renderComponent($slots[0] ? $slots[0] : 'HomePage_Slot16', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    return $_content;
   
}
