<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderNotFoundComponent_SlotContent7(
    \NotFoundComponent $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        <title>';
    $_content .= htmlentities($_component->title);
    $_content .= '</title>
    ';
    return $_content;
   
}
