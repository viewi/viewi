<?php

use Viewi\PageEngine;
use Viewi\BaseComponent;

function RenderAppComponent_SlotContent4(
    Silly\MyApp\AppComponent $_component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
      <a href="/front.php">Front dev</a>
   ';
    return $_content;
   
}
