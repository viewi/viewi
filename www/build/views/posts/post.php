<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderPostPage(
    \PostPage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $slotContents['head'] = 'PostPage_SlotContent4';

    $slotContents['body'] = 'PostPage_SlotContent5';

    $slotContents[0] = 'PostPage_Slot6';
    $_content .= $pageEngine->renderComponent('Layout', $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    return $_content;
   
}
