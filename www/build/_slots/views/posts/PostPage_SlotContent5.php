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
        ';
    if(!$_component->post){
    
    $_content .= '<h2>Loading..</h2>';
    } else {
    
    $_content .= '
            <h2>';
    $_content .= htmlentities($_component->title);
    $_content .= ' ';
    $_content .= htmlentities($_component->post->name);
    $_content .= '</h2>
            <div>';
    $_content .= htmlentities($_component->post->date);
    $_content .= '</div>
            <div>
                ';
    $_content .= htmlentities($_component->post->content);
    $_content .= '
            </div>
        ';
    }
    
    $_content .= '
    ';
    return $_content;
   
}
