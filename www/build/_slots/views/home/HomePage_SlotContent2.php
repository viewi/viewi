<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderHomePage_SlotContent2(
    \HomePage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        ';
    $slotContents[0] = 'HomePage_Slot3';
    $_content .= $pageEngine->renderComponent('UserItem', $_component, $slotContents, [
'user' => $_component->friend,
'title' => 'My custom title',
'active' => $_component->false,
], ...$scope);
    $slotContents = [];
    $_content .= '
        <br/>
        ';
    if($pageEngine->isTag(htmlentities($_component->dynamicName))) {
    $_content .= '<';
    $_content .= htmlentities($_component->dynamicName);
    $_content .= ' user="';
    $_content .= htmlentities($_component->friend);
    $_content .= '" title="';
    $_content .= htmlentities($_component->count);
    $_content .= ' ';
    $_content .= htmlentities($_component->title);
    $_content .= '" active="';
    $_content .= htmlentities($_component->false);
    $_content .= '">DYNAMIC TAG</';
    $_content .= htmlentities($_component->dynamicName);
    $_content .= '>';
    } else {
    $slotContents[0] = 'HomePage_Slot4';
    $_content .= $pageEngine->renderComponent($_component->dynamicName, $_component, $slotContents, [
'user' => $_component->friend,
'title' => $_component->count . ' ' . $_component->title,
'active' => $_component->false,
], ...$scope);
    $slotContents = [];
    $_content .= '';
    }
    $_content .= '

        <div class="passing-data-';
    $_content .= htmlentities($_component->count);
    $_content .= '">
        </div>

        ';
    $slotContents[0] = false;
    $_content .= $pageEngine->renderComponent('UserItem', $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
        ';
    if($pageEngine->isTag(htmlentities($_component->dynamicName))) {
    $_content .= '<';
    $_content .= htmlentities($_component->dynamicName);
    $_content .= '>Dynamic 1</';
    $_content .= htmlentities($_component->dynamicName);
    $_content .= '>';
    } else {
    $slotContents[0] = 'HomePage_Slot5';
    $_content .= $pageEngine->renderComponent($_component->dynamicName, $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '';
    }
    $_content .= '
        <div>
            ';
    if($pageEngine->isTag(htmlentities($_component->dynamicName))) {
    $_content .= '<';
    $_content .= htmlentities($_component->dynamicName);
    $_content .= '>Dynamic 2</';
    $_content .= htmlentities($_component->dynamicName);
    $_content .= '>';
    } else {
    $slotContents[0] = 'HomePage_Slot6';
    $_content .= $pageEngine->renderComponent($_component->dynamicName, $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '';
    }
    $_content .= '
        </div>
        <h4>
            Count: ';
    $_content .= htmlentities($_component->countState->count);
    $_content .= '
        </h4>
        <div class="my-class count-';
    $_content .= htmlentities($_component->count);
    $_content .= '';
    $_content .= htmlentities($_component->title ? ' show' : '');
    $_content .= '';
    $_content .= htmlentities($_component->title ? ' lg' : '');
    $_content .= '">
            Another count: ';
    $_content .= htmlentities($_component->count);
    $_content .= '
            <button>Increment</button>
        </div>
    ';
    return $_content;
   
}
