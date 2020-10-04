<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderLayout(
    \Layout $_component,
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
    $slotContents[0] = 'Layout_Slot11';
    $_content .= $pageEngine->renderComponent($slots['head'] ? $slots['head'] : 'Layout_Slot11', $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
    <style>
        body {
            background-color: rgb(51, 39, 39);
            color: rgb(104, 199, 202);
        }

        a {
            color: antiquewhite;
        }

        button {
            border: none;
            padding: 6px 12px;
            background-color: antiquewhite;
            color: brown;
            border-radius: 4px;
            outline: none;
        }

        button:hover {
            background-color: rgb(252, 243, 232);
            cursor: pointer;
        }

        button:active {
            background-color: rgb(252, 228, 197);
        }
    </style>

    <script src="/public/app/app.js"></script>
    <script src="/public/build/bundle.js"></script>
</head>

<body>
    ====================================================
    <br/>
    <b>Layout ';
    $_content .= htmlentities($_component->observableSubject->countState->count);
    $_content .= '</b>
    ';
    $_content .= $pageEngine->renderComponent($slots['body'], $_component, [], []); 
    $_content .= '
    ';
    $_content .= $pageEngine->renderComponent($slots[0], $_component, [], []); 
    $_content .= '
</body>

</html>';
    return $_content;
   
}
