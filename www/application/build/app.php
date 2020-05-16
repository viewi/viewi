<?php

function RenderAppComponent(AppComponent $component, PageEngine $pageEngine, array $slots
    , ...$scope
)
{
    $slotContents = [];
    ?><?php
    $slotContents['head'] = 'AppComponentSlotContent1';
?><?php
    $slotContents['body'] = 'AppComponentSlotContent2';
?><?php
    $slotContents[0] = 'AppComponentSlot11';
    $pageEngine->renderComponent('Layout', $component, $slotContents, ...$scope);
?><?php   
}
