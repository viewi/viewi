<?php

use Vo\PageEngine;

function ReadComponentsInfo(PageEngine $pageEngine)
{
    $pageEngine->setComponentsInfo(array (
  'CountState' => 
  array (
    'Name' => 'CountState',
    'Namespace' => '',
    'Fullpath' => '\\services\\CountState.php',
    'IsComponent' => false,
  ),
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
    'Dependencies' => 
    array (
      'countState' => 
      array (
        'name' => 'CountState',
      ),
    ),
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
      'notificationService2' => 
      array (
        'name' => 'NotificationService',
        'null' => 1,
      ),
      'notificationService3' => 
      array (
        'name' => 'NotificationService',
        'null' => 1,
      ),
      'notificationService4' => 
      array (
        'name' => 'NotificationService',
        'null' => 1,
      ),
      'f' => 
      array (
        'name' => 'float',
        'optional' => 1,
        'default' => 30,
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
      'test2' => 
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
      'test3' => 
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
      'test4' => 
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
      'order' => 1,
      'user' => 1,
      'title' => 1,
      'active' => 1,
    ),
  ),
  'ObservableService' => 
  array (
    'Name' => 'ObservableService',
    'Namespace' => '',
    'Fullpath' => '\\services\\ObservableService.php',
    'IsComponent' => false,
    'Dependencies' => 
    array (
      'countState' => 
      array (
        'name' => 'CountState',
      ),
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
    'Dependencies' => 
    array (
      'observableSubject' => 
      array (
        'name' => 'ObservableService',
      ),
    ),
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
  'HomePage_Slot4' => 
  array (
    'Name' => 'HomePage_Slot4',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage_Slot4',
    'Fullpath' => '\\views\\home\\home.php',
    'TemplatePath' => '\\views\\home\\HomePage_Slot4.html',
    'BuildPath' => '\\_slots\\views\\home\\HomePage_Slot4.php',
    'RenderFunction' => 'RenderHomePage_Slot4',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'HomePage_Slot5' => 
  array (
    'Name' => 'HomePage_Slot5',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage_Slot5',
    'Fullpath' => '\\views\\home\\home.php',
    'TemplatePath' => '\\views\\home\\HomePage_Slot5.html',
    'BuildPath' => '\\_slots\\views\\home\\HomePage_Slot5.php',
    'RenderFunction' => 'RenderHomePage_Slot5',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'HomePage_Slot6' => 
  array (
    'Name' => 'HomePage_Slot6',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage_Slot6',
    'Fullpath' => '\\views\\home\\home.php',
    'TemplatePath' => '\\views\\home\\HomePage_Slot6.html',
    'BuildPath' => '\\_slots\\views\\home\\HomePage_Slot6.php',
    'RenderFunction' => 'RenderHomePage_Slot6',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'HomePage_Slot7' => 
  array (
    'Name' => 'HomePage_Slot7',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage_Slot7',
    'Fullpath' => '\\views\\home\\home.php',
    'TemplatePath' => '\\views\\home\\HomePage_Slot7.html',
    'BuildPath' => '\\_slots\\views\\home\\HomePage_Slot7.php',
    'RenderFunction' => 'RenderHomePage_Slot7',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_SlotContent8' => 
  array (
    'Name' => 'AppComponent_SlotContent8',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_SlotContent8',
    'Fullpath' => '\\views\\app.php',
    'TemplatePath' => '\\views\\AppComponent_SlotContent8.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_SlotContent8.php',
    'RenderFunction' => 'RenderAppComponent_SlotContent8',
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
  'UserItem_Slot10' => 
  array (
    'Name' => 'UserItem_Slot10',
    'Namespace' => '',
    'ComponentName' => 'UserItem',
    'Tag' => 'UserItem_Slot10',
    'Fullpath' => '\\views\\home\\userItem.php',
    'TemplatePath' => '\\views\\home\\UserItem_Slot10.html',
    'BuildPath' => '\\_slots\\views\\home\\UserItem_Slot10.php',
    'RenderFunction' => 'RenderUserItem_Slot10',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'Layout_Slot11' => 
  array (
    'Name' => 'Layout_Slot11',
    'Namespace' => '',
    'ComponentName' => 'Layout',
    'Tag' => 'Layout_Slot11',
    'Fullpath' => '\\views\\layouts\\layout.php',
    'TemplatePath' => '\\views\\layouts\\Layout_Slot11.html',
    'BuildPath' => '\\_slots\\views\\layouts\\Layout_Slot11.php',
    'RenderFunction' => 'RenderLayout_Slot11',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
));   
}
