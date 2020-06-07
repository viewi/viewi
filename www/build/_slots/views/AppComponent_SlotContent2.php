<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_SlotContent2(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
        ';
    $slotContents[0] = 'AppComponent_Slot3';
    $_content .= $pageEngine->renderComponent('UserItem', $component, $slotContents, [
'title' => 'User item title',
], ...$scope);
    $slotContents = [];
    $_content .= '
        ';
    foreach($component->users as $uid => $user){
    
    $slotContents[0] = 'AppComponent_Slot4';
    $_content .= $pageEngine->renderComponent('UserItem', $component, $slotContents, [
'user' => $user,
'active' => true,
'title' => $component->getFullName(),
], $uid, $user);
    $slotContents = [];
    }
    
    $_content .= '
        ';
    $_content .= $component->html;
    $_content .= '
        ';
    $_content .= htmlentities($component->html);
    $_content .= '
        ';
    foreach($component->users as $uid => $user){
    
    if($user->Age < 32){
    
    $_content .= '
            Template user-';
    $_content .= htmlentities($uid);
    $_content .= '
        ';
    }
    
    }
    
    $_content .= '
        ';
    if($component->true){
    
    $_content .= '<div>
            true
        </div>';
    }
    
    $_content .= '
        ';
    if($component->false){
    
    $_content .= '<div>
            false
        </div>';
    }
    
    $_content .= '
        ';
    if(true){
    
    $_content .= '<div>
            true
        </div>';
    }
    
    $_content .= '
        ';
    if(false){
    
    $_content .= '<div>
            false
        </div>';
    }
    
    $_content .= '
        ';
    foreach($component->users as $uid => $user){
    
    $slotContents[0] = 'AppComponent_Slot5';
    $_content .= $pageEngine->renderComponent('UserItem', $component, $slotContents, [], $uid, $user);
    $slotContents = [];
    }
    
    $_content .= '
        ';
    foreach($component->users as $uid => $user){
    
    $_content .= '<div>
            Div: ';
    $slotContents[0] = 'AppComponent_Slot6';
    $_content .= $pageEngine->renderComponent('UserItem', $component, $slotContents, [], $uid, $user);
    $slotContents = [];
    $_content .= '
        </div>';
    }
    
    $_content .= '
        <ul>
            ';
    foreach($component->users as $uid => $user){
    
    $_content .= '<li>
                ';
    $_content .= htmlentities($uid);
    $_content .= ' ';
    $_content .= htmlentities($user->Name);
    $_content .= ' ';
    $_content .= htmlentities($user->Age);
    $_content .= '
            </li>';
    }
    
    $_content .= '
        </ul>
        <ul>
            ';
    foreach($component->users as $uid=>$user){
    
    $_content .= '<li>
                ';
    $_content .= htmlentities($uid);
    $_content .= ' ';
    $_content .= htmlentities($user->Name);
    $_content .= ' ';
    $_content .= htmlentities($user->Age);
    $_content .= '
            </li>';
    }
    
    $_content .= '
        </ul>
        <ul>
            ';
    foreach($component->testsArray as $item){
    
    $_content .= '<li>
                ';
    $_content .= htmlentities($item);
    $_content .= '
            </li>';
    }
    
    $_content .= '
        </ul>

        <span class="my-class ';
    $_content .= htmlentities($component->className);
    $_content .= '';
    $_content .= htmlentities($component->false ? ' show' : '');
    $_content .= '';
    $_content .= htmlentities($component->true ? ' active' : '');
    $_content .= '">
            attribute merge
        </span>
        <p>=====</p>
        <a>=====</a>
        <b>=====</b>
        <span class="my-class"';
    $_content .= $component->true ? ' disabled="disabled"' : '';
    $_content .= $component->false ? ' checked="checked"' : '';
    $_content .= '>
            BOOLEAN attributes
        </span>
        ';
    $slotContents[0] = 'AppComponent_Slot7';
    $_content .= $pageEngine->renderComponent($component->dynamicTag, $component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
        ';
    $slotContents[0] = 'AppComponent_Slot8';
    $_content .= $pageEngine->renderComponent('HomePage', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
        ';
    $_content .= htmlentities((4+5)*3 + 4/((5+4)-1));
    $_content .= '
        <a src="';
    $_content .= htmlentities($component->url);
    $_content .= '">Home page</a>
        <br/>
        "testing =*&%#@!)(*&" / 30$ > 15$ & 10 \\
        \\
        < 20 {O_o} hello \' ""\' \\noescape <hr/>
        Content text "test string" \'another one\' done.
        <div data-name="test name is &quot;Mike&quot;." title="My dad\'s story">';
    $_content .= htmlentities($component->testsList[1]);
    $_content .= '</div>
        <p ';
    $_content .= htmlentities($component->dynamicAttr);
    $_content .= '="';
    $_content .= htmlentities($component->dynValue);
    $_content .= '">';
    $_content .= htmlentities($component->model);
    $_content .= 'Model</p>
        ';
    $_content .= htmlentities($component->about);
    $_content .= '
        <div title="Title of ';
    $_content .= htmlentities($component->model);
    $_content .= ' model">title test</div>
        <p class="long-text test" title="Awesome">paragraph text</p>
        <a><b>text</b></a>
        <br attr="some value" data-test="hedge"/>
        <div>
            ';
    $_content .= htmlentities($component->about);
    $_content .= ' ';
    $_content .= htmlentities($component->testsList[0]);
    $_content .= '
            <div>My friend ';
    $_content .= htmlentities($component->friend->Name);
    $_content .= ' is ';
    $_content .= htmlentities($component->friend->Age);
    $_content .= ' years old</div>
            <p>';
    $_content .= htmlentities($component->model);
    $_content .= 'Model</p>
            <p>Name: ';
    $_content .= htmlentities($component->getFullName());
    $_content .= '</p>
            <p>Occupation: ';
    $_content .= htmlentities($component->getOccupation());
    $_content .= '</p>
        </div>
        <br/>
        <b>1 + 1 is ';
    $_content .= htmlentities(1+1);
    $_content .= '</b>
        <header aria-disabled id="my-header"></header>
        <f:table xmlns:f="https://www.w3schools.com/furniture">
            <f:name>Xml parsing demo</f:name>
            <f:width>80</f:width>
            <f:length>120</f:length>
        </f:table>
        plain text
        <b>test</b>
        <h1>
            header
        </h1>
        ';
    $slotContents[0] = false;
    $_content .= $pageEngine->renderComponent('HomePage', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
        <hr/>
        ';
    $slotContents[0] = 'AppComponent_Slot12';
    $_content .= $pageEngine->renderComponent('HomePage', $component, $slotContents, [], ...$scope);
    $slotContents = [];
    $_content .= '
        <footer>Footer</footer>
    ';
    return $_content;
   
}
