<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderHomePage(
    \HomePage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $slotContents['head'] = 'HomePage_SlotContent1';

    $slotContents['body'] = 'HomePage_SlotContent2';

    $slotContents[0] = 'HomePage_Slot3';
    $_content .= $pageEngine->renderComponent('Layout', $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    return $_content;
   
}
