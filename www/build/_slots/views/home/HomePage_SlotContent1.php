<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderHomePage_SlotContent1(
    \HomePage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        <!-- <tag></tag> <!-- another text -> my comment ';
    $_content .= htmlentities($_component->title);
    $_content .= ' -->
        <title>';
    $_content .= htmlentities($_component->count);
    $_content .= ' ';
    $_content .= htmlentities($_component->title);
    $_content .= '</title>
    ';
    return $_content;
   
}
