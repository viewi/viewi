<?php

use Vo\PageEngine;
use Vo\BaseComponent;

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
