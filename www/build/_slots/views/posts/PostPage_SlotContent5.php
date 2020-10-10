<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderPostPage_SlotContent5(
    \PostPage $_component,
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
