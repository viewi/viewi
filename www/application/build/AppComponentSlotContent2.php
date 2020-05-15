<?php

function RenderAppComponentSlotContent2(AppComponent $component, PageEngine $pageEngine, array $slots)
{
    $slotContents = [];
    ?>
        <span class="my-class <?=htmlentities($component->className)?><?=htmlentities($component->false ? ' show' : '')?><?=htmlentities($component->true ? ' active' : '')?>">

            attribute merge
        </span>
        <span class="my-class"<?=$component->true ? ' disabled="disabled"' : ''?><?=$component->false ? ' checked="checked"' : ''?>>
            BOOLEAN attributes
        </span>
        <?php
    $slotContents[0] = 'AppComponentSlot3';
    $pageEngine->renderComponent($component->dynamicTag, $component, $slotContents);
?>

        <?php
    $slotContents[0] = 'AppComponentSlot4';
    $pageEngine->renderComponent('HomePage', $component, $slotContents);
?>

        <?=htmlentities((4+5)*3 + 4/((5+4)-1))?>

        <a src="<?=htmlentities($component->url)?>">Home page</a>
        <br/>
        "testing =*&%#@!)(*&" / 30$ > 15$ & 10 \
        \
        < 20 {O_o} hello ' ""' \noescape <hr/>
        Content text "test string" 'another one' done.
        <div data-name="test name is &quot;Mike&quot;." title="My dad's story"><?=htmlentities($component->testsList[1])?></div>
        <p <?=htmlentities($component->dynamicAttr)?>="<?=htmlentities($component->dynValue)?>"><?=htmlentities($component->model)?>Model</p>
        <?=htmlentities($component->about)?>

        <div title="Title of <?=htmlentities($component->model)?> model">title test</div>
        <p class="long-text test" title="Awesome">paragraph text</p>
        <a><b>text</b></a>
        <br attr="some value" data-test="hedge"/>
        <div>
            <?=htmlentities($component->about)?> <?=htmlentities($component->testsList[0])?>

            <div>My friend <?=htmlentities($component->friend->Name)?> is <?=htmlentities($component->friend->Age)?> years old</div>
            <p><?=htmlentities($component->model)?>Model</p>
            <p>Name: <?=htmlentities($component->getFullName())?></p>
            <p>Occupation: <?=htmlentities($component->getOccupation())?></p>
        </div>
        <br/>
        <b>1 + 1 is <?=htmlentities(1+1)?></b>
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
        <?php
    $pageEngine->renderComponent('HomePage', $component, $slotContents);
?>

        <hr/>
        <?php
    $slotContents[0] = 'AppComponentSlot8';
    $pageEngine->renderComponent('HomePage', $component, $slotContents);
?>

        <footer>Footer</footer>
    <?php   
}
