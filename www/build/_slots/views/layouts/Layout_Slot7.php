<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderLayout_Slot7(
    \Layout $_component,
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
