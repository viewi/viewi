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
    if($pageEngine->isTag(htmlentities($_component->htag))) {
    $_content .= '<';
    $_content .= htmlentities($_component->htag);
    $_content .= ' class="big-title">';
    $_content .= htmlentities($_component->title);
    $_content .= '</';
    $_content .= htmlentities($_component->htag);
    $_content .= '>';
    } else {
    $slotContents[0] = 'HomePage_Slot3';
    $_content .= $pageEngine->renderComponent($_component->htag, $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '';
    }
    $_content .= '
        ';
    if($pageEngine->isTag(htmlentities($_component->htag))) {
    $_content .= '<';
    $_content .= htmlentities($_component->htag);
    $_content .= ' class="big-title-2"></';
    $_content .= htmlentities($_component->htag);
    $_content .= '>';
    } else {
    $slotContents[0] = 'HomePage_Slot4';
    $_content .= $pageEngine->renderComponent($_component->htag, $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '';
    }
    $_content .= '
        ';
    $slotContents[0] = 'HomePage_Slot5';
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
    $slotContents[0] = 'HomePage_Slot6';
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
    $slotContents[0] = 'HomePage_Slot7';
    $_content .= $pageEngine->renderComponent($_component->dynamicName, $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '';
    }
    $_content .= '
        </div>
        <h4>
            Count: ';
    $_content .= htmlentities($_component->count);
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
        <p>
            ';
    foreach($_component->fruits as $fruit){
    
    $_content .= '<b>Fruit: ';
    $_content .= htmlentities($fruit);
    $_content .= ' </b>';
    }
    
    $_content .= '
            ';
    if($_component->count % 2 === 0){
    
    $_content .= '<i>';
    $_content .= htmlentities($_component->count);
    $_content .= ' is Odd</i>';
    } else {
    
    $_content .= '<span>';
    $_content .= htmlentities($_component->count);
    $_content .= ' is Even</span>';
    }
    
    $_content .= '
        </p>
    ';
    return $_content;
   
}
