<?php

function RenderAppComponent(
    AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , ...$scope
) {
    $slotContents = [];
    ?><?php
    $slotContents['head'] = 'AppComponent_SlotContent1';
?><?php
    $slotContents['body'] = 'AppComponent_SlotContent2';
?><?php
    $slotContents[0] = 'AppComponent_Slot11';
    $pageEngine->renderComponent('Layout', $component, $slotContents, ...$scope);
?><?php   
}
