<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderNotFoundComponent_SlotContent8(
    \NotFoundComponent $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        <h2>';
    $_content .= htmlentities($_component->title);
    $_content .= '</h2>
    ';
    return $_content;
   
}
