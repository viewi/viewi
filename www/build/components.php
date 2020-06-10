<?php

use Vo\PageEngine;

function ReadComponentsInfo(PageEngine $pageEngine)
{
    $pageEngine->setComponentsInfo(array (
  'HomePage' => 
  array (
    'Name' => 'HomePage',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage',
    'Fullpath' => '\\views\\home\\home.php',
    'TemplatePath' => '\\views\\home\\home.html',
    'BuildPath' => '\\views\\home\\home.php',
    'RenderFunction' => 'RenderHomePage',
    'IsComponent' => true,
  ),
  'NotificationService' => 
  array (
    'Name' => 'NotificationService',
    'Namespace' => '',
    'Fullpath' => '\\services\\NotificationService.php',
    'IsComponent' => false,
    'Dependencies' => 
    array (
      'http' => 
      array (
        'name' => 'HttpClientService',
      ),
    ),
  ),
  'HttpClientService' => 
  array (
    'Name' => 'HttpClientService',
    'Namespace' => 'HttpTools',
    'Fullpath' => '\\services\\HttpClientService.php',
    'IsComponent' => false,
  ),
  'AppComponent' => 
  array (
    'Name' => 'AppComponent',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\app.html',
    'BuildPath' => '\\views\\app.php',
    'RenderFunction' => 'RenderAppComponent',
    'IsComponent' => true,
    'Dependencies' => 
    array (
      'notificationService' => 
      array (
        'name' => 'NotificationService',
      ),
      'http' => 
      array (
        'name' => 'HttpClientService',
      ),
      'name' => 
      array (
        'name' => 'string',
        'builtIn' => 1,
      ),
      'cost' => 
      array (
        'name' => 'int',
        'builtIn' => 1,
        'null' => 1,
      ),
      'ns' => 
      array (
        'name' => 'NotificationService',
        'null' => 1,
      ),
      'f' => 
      array (
        'name' => 'float',
        'optional' => 1,
        'default' => 3,
        'builtIn' => 1,
        'null' => 1,
      ),
      'test' => 
      array (
        'name' => 'array',
        'optional' => 1,
        'default' => 
        array (
          0 => 5,
          1 => 6,
        ),
        'builtIn' => 1,
        'null' => 1,
      ),
    ),
  ),
  'UserItem' => 
  array (
    'Name' => 'UserItem',
    'Namespace' => '',
    'ComponentName' => 'UserItem',
    'Tag' => 'UserItem',
    'Fullpath' => '\\views\\home\\userItem.php',
    'TemplatePath' => '\\views\\home\\userItem.html',
    'BuildPath' => '\\views\\home\\userItem.php',
    'RenderFunction' => 'RenderUserItem',
    'IsComponent' => true,
    'Inputs' => 
    array (
      'title' => 1,
      'user' => 1,
      'active' => 1,
    ),
  ),
  'Layout' => 
  array (
    'Name' => 'Layout',
    'Namespace' => '',
    'ComponentName' => 'Layout',
    'Tag' => 'Layout',
    'Fullpath' => '\\views\\layouts\\layout.php',
    'TemplatePath' => '\\views\\layouts\\layout.html',
    'BuildPath' => '\\views\\layouts\\layout.php',
    'RenderFunction' => 'RenderLayout',
    'IsComponent' => true,
  ),
  'ErrorInterceptor' => 
  array (
    'Name' => 'ErrorInterceptor',
    'Namespace' => '',
    'Fullpath' => '\\services\\ErrorInterceptor.php',
    'IsComponent' => false,
    'Dependencies' => 
    array (
      'http' => 
      array (
        'name' => 'HttpClientService',
      ),
    ),
  ),
  'Friend' => 
  array (
    'Name' => 'Friend',
    'Namespace' => 'Silly\\MyApp',
    'Fullpath' => '\\views\\app.php',
    'IsComponent' => false,
  ),
  'HomePage_SlotContent1' => 
  array (
    'Name' => 'HomePage_SlotContent1',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage_SlotContent1',
    'Fullpath' => '\\views\\home\\home.php',
    'TemplatePath' => '\\views\\home\\HomePage_SlotContent1.html',
    'BuildPath' => '\\_slots\\views\\home\\HomePage_SlotContent1.php',
    'RenderFunction' => 'RenderHomePage_SlotContent1',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'HomePage_SlotContent2' => 
  array (
    'Name' => 'HomePage_SlotContent2',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage_SlotContent2',
    'Fullpath' => '\\views\\home\\home.php',
    'TemplatePath' => '\\views\\home\\HomePage_SlotContent2.html',
    'BuildPath' => '\\_slots\\views\\home\\HomePage_SlotContent2.php',
    'RenderFunction' => 'RenderHomePage_SlotContent2',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'HomePage_Slot3' => 
  array (
    'Name' => 'HomePage_Slot3',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage_Slot3',
    'Fullpath' => '\\views\\home\\home.php',
    'TemplatePath' => '\\views\\home\\HomePage_Slot3.html',
    'BuildPath' => '\\_slots\\views\\home\\HomePage_Slot3.php',
    'RenderFunction' => 'RenderHomePage_Slot3',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_SlotContent4' => 
  array (
    'Name' => 'AppComponent_SlotContent4',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_SlotContent4',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_SlotContent4.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_SlotContent4.php',
    'RenderFunction' => 'RenderAppComponent_SlotContent4',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_SlotContent5' => 
  array (
    'Name' => 'AppComponent_SlotContent5',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_SlotContent5',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_SlotContent5.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_SlotContent5.php',
    'RenderFunction' => 'RenderAppComponent_SlotContent5',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot6' => 
  array (
    'Name' => 'AppComponent_Slot6',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot6',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot6.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot6.php',
    'RenderFunction' => 'RenderAppComponent_Slot6',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot7' => 
  array (
    'Name' => 'AppComponent_Slot7',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot7',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot7.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot7.php',
    'RenderFunction' => 'RenderAppComponent_Slot7',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot8' => 
  array (
    'Name' => 'AppComponent_Slot8',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot8',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot8.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot8.php',
    'RenderFunction' => 'RenderAppComponent_Slot8',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot9' => 
  array (
    'Name' => 'AppComponent_Slot9',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot9',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot9.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot9.php',
    'RenderFunction' => 'RenderAppComponent_Slot9',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot10' => 
  array (
    'Name' => 'AppComponent_Slot10',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot10',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot10.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot10.php',
    'RenderFunction' => 'RenderAppComponent_Slot10',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot11' => 
  array (
    'Name' => 'AppComponent_Slot11',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot11',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot11.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot11.php',
    'RenderFunction' => 'RenderAppComponent_Slot11',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot12' => 
  array (
    'Name' => 'AppComponent_Slot12',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot12',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot12.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot12.php',
    'RenderFunction' => 'RenderAppComponent_Slot12',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot13' => 
  array (
    'Name' => 'AppComponent_Slot13',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot13',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot13.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot13.php',
    'RenderFunction' => 'RenderAppComponent_Slot13',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot14' => 
  array (
    'Name' => 'AppComponent_Slot14',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot14',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot14.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot14.php',
    'RenderFunction' => 'RenderAppComponent_Slot14',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot15' => 
  array (
    'Name' => 'AppComponent_Slot15',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot15',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot15.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot15.php',
    'RenderFunction' => 'RenderAppComponent_Slot15',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot16' => 
  array (
    'Name' => 'AppComponent_Slot16',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot16',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot16.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot16.php',
    'RenderFunction' => 'RenderAppComponent_Slot16',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot17' => 
  array (
    'Name' => 'AppComponent_Slot17',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot17',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot17.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot17.php',
    'RenderFunction' => 'RenderAppComponent_Slot17',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_Slot18' => 
  array (
    'Name' => 'AppComponent_Slot18',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_Slot18',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_Slot18.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot18.php',
    'RenderFunction' => 'RenderAppComponent_Slot18',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'UserItem_Slot19' => 
  array (
    'Name' => 'UserItem_Slot19',
    'Namespace' => '',
    'ComponentName' => 'UserItem',
    'Tag' => 'UserItem_Slot19',
    'Fullpath' => '\\views\\home\\userItem.php',
    'TemplatePath' => '\\views\\home\\UserItem_Slot19.html',
    'BuildPath' => '\\_slots\\views\\home\\UserItem_Slot19.php',
    'RenderFunction' => 'RenderUserItem_Slot19',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'Layout_Slot20' => 
  array (
    'Name' => 'Layout_Slot20',
    'Namespace' => '',
    'ComponentName' => 'Layout',
    'Tag' => 'Layout_Slot20',
    'Fullpath' => '\\views\\layouts\\layout.php',
    'TemplatePath' => '\\views\\layouts\\Layout_Slot20.html',
    'BuildPath' => '\\_slots\\views\\layouts\\Layout_Slot20.php',
    'RenderFunction' => 'RenderLayout_Slot20',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
));   
}
