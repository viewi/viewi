<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderLayout_Slot18(
    \Layout $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        <title>';
    $_content .= htmlentities($component->title);
    $_content .= '</title>
    ';
    return $_content;
   
}
