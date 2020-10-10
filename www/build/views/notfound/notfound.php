<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderNotFoundComponent(
    \NotFoundComponent $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $slotContents['head'] = 'NotFoundComponent_SlotContent7';

    $slotContents['body'] = 'NotFoundComponent_SlotContent8';

    $slotContents[0] = 'NotFoundComponent_Slot9';
    $_content .= $pageEngine->renderComponent('Layout', $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    return $_content;
   
}
