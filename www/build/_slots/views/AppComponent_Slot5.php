<?php

use Vo\PageEngine;
use Vo\BaseComponent;

function RenderAppComponent_Slot5(
    Silly\MyApp\AppComponent $component,
    PageEngine $pageEngine,
    array $slots
    , $uid, $user
) {
    $slotContents = [];
    
    $_content = '';

    $_content .= '
            user-';
    $_content .= htmlentities($uid);
    $_content .= '
            ';
    if($user->Age < 31){
    
    $_content .= '<div>
                <b>
                    Age < 31: ';
    $_content .= htmlentities($uid);
    $_content .= ' ';
    $_content .= htmlentities($user->Name);
    $_content .= ' ';
    $_content .= htmlentities($user->Age);
    $_content .= '
                </b>
            </div>';
    } else if ($user->Name === 'Jhonz Doeb'){
    
    $_content .= '<div>
                <b>
                    else-if Jhonz Doeb: ';
    $_content .= htmlentities($uid);
    $_content .= ' ';
    $_content .= htmlentities($user->Name);
    $_content .= ' ';
    $_content .= htmlentities($user->Age);
    $_content .= '
                </b>
            </div>';
    } else if ($user->Age === 32){
    
    $_content .= '<div>
                <b>
                    else-if age 32: ';
    $_content .= htmlentities($uid);
    $_content .= ' ';
    $_content .= htmlentities($user->Name);
    $_content .= ' ';
    $_content .= htmlentities($user->Age);
    $_content .= '
                </b>
            </div>';
    } else {
    
    $_content .= '<div>
                <b>
                    else: ';
    $_content .= htmlentities($uid);
    $_content .= ' ';
    $_content .= htmlentities($user->Name);
    $_content .= ' ';
    $_content .= htmlentities($user->Age);
    $_content .= '
                </b>
            </div>';
    }
    
    $_content .= '

        ';
    return $_content;
   
}
