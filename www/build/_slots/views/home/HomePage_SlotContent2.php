<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderHomePage_SlotContent2(
    \HomePage $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        <div id="customJsTestId">

        </div>
        <div ';
    if ($_component->attrName[0] !== '(') {
    $_content .= htmlentities($_component->attrName);
    $_content .= '="My title"';
    }
    $_content .= '>
            ';
    if($pageEngine->isTag(htmlentities($_component->dynamicName))) {
    $_content .= '<';
    $_content .= htmlentities($_component->dynamicName);
    $_content .= '></';
    $_content .= htmlentities($_component->dynamicName);
    $_content .= '>';
    } else {
    $slotContents[0] = false;
    $_content .= $pageEngine->renderComponent($_component->dynamicName, $_component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '';
    }
    $_content .= '
            ';
    $slotContents[0] = false;
    $_content .= $pageEngine->renderComponent('UserItem', $_component, $slotContents, [], ...$scope);
    $slotContents = [];
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
        <div>
            ';
    foreach($_component->fruits as $fruit){
    
    $_content .= '<b>
                ';
    $_content .= htmlentities($fruit);
    $_content .= '
            </b>';
    }
    
    $_content .= '
        </div>
        <b ';
    if ($_component->dynamicEvent[0] !== '(') {
    $_content .= htmlentities($_component->dynamicEvent);
    $_content .= '="Increment()"';
    }
    $_content .= '>Dynamic event ';
    $_content .= htmlentities($_component->dynamicEvent);
    $_content .= '</b>
    ';
    return $_content;
   
}
