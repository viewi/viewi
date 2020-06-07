<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderLayout(
    \Layout $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '<!DOCTYPE html>
<html>

<head>
    ';
    $slotContents[0] = 'Layout_Slot18';
    $_content .= $pageEngine->renderComponent($slots['head'] ? $slots['head'] : 'Layout_Slot18', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
</head>

<body>
    ';
    $_content .= $pageEngine->renderComponent($slots['body'], $component, [], []); 
    $_content .= '
    ';
    $_content .= $pageEngine->renderComponent($slots[0], $component, [], []); 
    $_content .= '
</body>

</html>';
    return $_content;
   
}
