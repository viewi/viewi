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
        <div>
            <input type="text" value="';
    $_content .= htmlentities($_component->fullName);
    $_content .= '" name="FullName"/>
            <p>Full Name: ';
    $_content .= htmlentities($_component->fullName);
    $_content .= '</p>
            <p><textarea value="';
    $_content .= htmlentities($_component->fullName);
    $_content .= '"></textarea></p>
        </div>
        <div>
            <input type="text" value="';
    $_content .= htmlentities($_component->friend->Name);
    $_content .= '" name="FriendName"/>
            <p>Friend Name: ';
    $_content .= htmlentities($_component->friend->Name);
    $_content .= '</p>
            <p><textarea value="';
    $_content .= htmlentities($_component->friend->Name);
    $_content .= '"></textarea></p>
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
