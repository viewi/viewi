<?php

function RenderLayout(Layout $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?><!DOCTYPE html>
<html>

<head>
    <?php
$slotContents[] = 'LayoutSlot11';
$pageEngine->renderComponent($slots['head'] ? $slots['head'] : 'LayoutSlot11', $component, $slotContents);
?>
</head>

<body>
    <?php $pageEngine->renderComponent($slots['body'], $component, []); ?>
</body>

</html><?php   
}
