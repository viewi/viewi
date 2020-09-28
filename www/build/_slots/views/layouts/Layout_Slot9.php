<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderLayout_Slot9(
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
