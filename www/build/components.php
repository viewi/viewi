<?php

use Viewi\PageEngine;

function ReadComponentsInfo(PageEngine $pageEngine)
{
    $pageEngine->setComponentsInfo(array (
  'CountState' => 
  array (
    'Name' => 'CountState',
    'Namespace' => '',
    'Fullpath' => '\\services\\CountState.php',
    'relative' => true,
    'IsComponent' => false,
    'hasInit' => false,
  ),
  'HomePage' => 
  array (
    'Name' => 'HomePage',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage',
    'Fullpath' => '\\views\\home\\home.php',
    'relative' => true,
    'TemplatePath' => '\\views\\home\\home.html',
    'BuildPath' => '\\views\\home\\home.php',
    'RenderFunction' => 'RenderHomePage',
    'IsComponent' => true,
    'hasInit' => true,
    'Dependencies' => 
    array (
      'countState' => 
      array (
        'name' => 'CountState',
      ),
    ),
  ),
  'HttpClient' => 
  array (
    'Name' => 'HttpClient',
    'Namespace' => 'Viewi\\Common',
    'Fullpath' => 'C:\\Users\\Ivan\\source\\repos\\pageless.local.com\\www\\core\\Viewi\\Common\\HttpClient.php',
    'relative' => false,
    'IsComponent' => false,
    'hasInit' => false,
  ),
  'PostPage' => 
  array (
    'Name' => 'PostPage',
    'Namespace' => '',
    'ComponentName' => 'PostPage',
    'Tag' => 'PostPage',
    'Fullpath' => '\\views\\posts\\post.php',
    'relative' => true,
    'TemplatePath' => '\\views\\posts\\post.html',
    'BuildPath' => '\\views\\posts\\post.php',
    'RenderFunction' => 'RenderPostPage',
    'IsComponent' => true,
    'hasInit' => true,
    'Dependencies' => 
    array (
      'http' => 
      array (
        'name' => 'HttpClient',
      ),
    ),
  ),
  'NotFoundComponent' => 
  array (
    'Name' => 'NotFoundComponent',
    'Namespace' => '',
    'ComponentName' => 'NotFoundComponent',
    'Tag' => 'NotFoundComponent',
    'Fullpath' => '\\views\\notfound\\notfound.php',
    'relative' => true,
    'TemplatePath' => '\\views\\notfound\\notfound.html',
    'BuildPath' => '\\views\\notfound\\notfound.php',
    'RenderFunction' => 'RenderNotFoundComponent',
    'IsComponent' => true,
    'hasInit' => false,
  ),
  'NotificationService' => 
  array (
    'Name' => 'NotificationService',
    'Namespace' => '',
    'Fullpath' => '\\services\\NotificationService.php',
    'relative' => true,
    'IsComponent' => false,
    'hasInit' => false,
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
    'relative' => true,
    'IsComponent' => false,
    'hasInit' => false,
  ),
  'AppComponent' => 
  array (
    'Name' => 'AppComponent',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent',
    'Fullpath' => '\\views\\app.php',
    'relative' => true,
    'TemplatePath' => '\\views\\app.html',
    'BuildPath' => '\\views\\app.php',
    'RenderFunction' => 'RenderAppComponent',
    'IsComponent' => true,
    'hasInit' => true,
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
    'relative' => true,
    'TemplatePath' => '\\views\\home\\userItem.html',
    'BuildPath' => '\\views\\home\\userItem.php',
    'RenderFunction' => 'RenderUserItem',
    'IsComponent' => true,
    'hasInit' => false,
  ),
  'ObservableService' => 
  array (
    'Name' => 'ObservableService',
    'Namespace' => '',
    'Fullpath' => '\\services\\ObservableService.php',
    'relative' => true,
    'IsComponent' => false,
    'hasInit' => false,
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
    'relative' => true,
    'TemplatePath' => '\\views\\layouts\\layout.html',
    'BuildPath' => '\\views\\layouts\\layout.php',
    'RenderFunction' => 'RenderLayout',
    'IsComponent' => true,
    'hasInit' => true,
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
    'relative' => true,
    'IsComponent' => false,
    'hasInit' => false,
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
    'relative' => true,
    'IsComponent' => false,
    'hasInit' => false,
  ),
  'HomePage_SlotContent1' => 
  array (
    'Name' => 'HomePage_SlotContent1',
    'Namespace' => '',
    'ComponentName' => 'HomePage',
    'Tag' => 'HomePage_SlotContent1',
    'Fullpath' => '\\views\\home\\home.php',
    'relative' => true,
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
    'relative' => true,
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
    'relative' => true,
    'TemplatePath' => '\\views\\home\\HomePage_Slot3.html',
    'BuildPath' => '\\_slots\\views\\home\\HomePage_Slot3.php',
    'RenderFunction' => 'RenderHomePage_Slot3',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'PostPage_SlotContent4' => 
  array (
    'Name' => 'PostPage_SlotContent4',
    'Namespace' => '',
    'ComponentName' => 'PostPage',
    'Tag' => 'PostPage_SlotContent4',
    'Fullpath' => '\\views\\posts\\post.php',
    'relative' => true,
    'TemplatePath' => '\\views\\posts\\PostPage_SlotContent4.html',
    'BuildPath' => '\\_slots\\views\\posts\\PostPage_SlotContent4.php',
    'RenderFunction' => 'RenderPostPage_SlotContent4',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'PostPage_SlotContent5' => 
  array (
    'Name' => 'PostPage_SlotContent5',
    'Namespace' => '',
    'ComponentName' => 'PostPage',
    'Tag' => 'PostPage_SlotContent5',
    'Fullpath' => '\\views\\posts\\post.php',
    'relative' => true,
    'TemplatePath' => '\\views\\posts\\PostPage_SlotContent5.html',
    'BuildPath' => '\\_slots\\views\\posts\\PostPage_SlotContent5.php',
    'RenderFunction' => 'RenderPostPage_SlotContent5',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'PostPage_Slot6' => 
  array (
    'Name' => 'PostPage_Slot6',
    'Namespace' => '',
    'ComponentName' => 'PostPage',
    'Tag' => 'PostPage_Slot6',
    'Fullpath' => '\\views\\posts\\post.php',
    'relative' => true,
    'TemplatePath' => '\\views\\posts\\PostPage_Slot6.html',
    'BuildPath' => '\\_slots\\views\\posts\\PostPage_Slot6.php',
    'RenderFunction' => 'RenderPostPage_Slot6',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'NotFoundComponent_SlotContent7' => 
  array (
    'Name' => 'NotFoundComponent_SlotContent7',
    'Namespace' => '',
    'ComponentName' => 'NotFoundComponent',
    'Tag' => 'NotFoundComponent_SlotContent7',
    'Fullpath' => '\\views\\notfound\\notfound.php',
    'relative' => true,
    'TemplatePath' => '\\views\\notfound\\NotFoundComponent_SlotContent7.html',
    'BuildPath' => '\\_slots\\views\\notfound\\NotFoundComponent_SlotContent7.php',
    'RenderFunction' => 'RenderNotFoundComponent_SlotContent7',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'NotFoundComponent_SlotContent8' => 
  array (
    'Name' => 'NotFoundComponent_SlotContent8',
    'Namespace' => '',
    'ComponentName' => 'NotFoundComponent',
    'Tag' => 'NotFoundComponent_SlotContent8',
    'Fullpath' => '\\views\\notfound\\notfound.php',
    'relative' => true,
    'TemplatePath' => '\\views\\notfound\\NotFoundComponent_SlotContent8.html',
    'BuildPath' => '\\_slots\\views\\notfound\\NotFoundComponent_SlotContent8.php',
    'RenderFunction' => 'RenderNotFoundComponent_SlotContent8',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'NotFoundComponent_Slot9' => 
  array (
    'Name' => 'NotFoundComponent_Slot9',
    'Namespace' => '',
    'ComponentName' => 'NotFoundComponent',
    'Tag' => 'NotFoundComponent_Slot9',
    'Fullpath' => '\\views\\notfound\\notfound.php',
    'relative' => true,
    'TemplatePath' => '\\views\\notfound\\NotFoundComponent_Slot9.html',
    'BuildPath' => '\\_slots\\views\\notfound\\NotFoundComponent_Slot9.php',
    'RenderFunction' => 'RenderNotFoundComponent_Slot9',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'AppComponent_SlotContent10' => 
  array (
    'Name' => 'AppComponent_SlotContent10',
    'Namespace' => 'Silly\\MyApp',
    'ComponentName' => 'AppComponent',
    'Tag' => 'AppComponent_SlotContent10',
    'Fullpath' => '\\views\\app.php',
    'relative' => true,
    'TemplatePath' => '\\views\\AppComponent_SlotContent10.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_SlotContent10.php',
    'RenderFunction' => 'RenderAppComponent_SlotContent10',
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
    'relative' => true,
    'TemplatePath' => '\\views\\AppComponent_Slot11.html',
    'BuildPath' => '\\_slots\\views\\AppComponent_Slot11.php',
    'RenderFunction' => 'RenderAppComponent_Slot11',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'UserItem_Slot12' => 
  array (
    'Name' => 'UserItem_Slot12',
    'Namespace' => '',
    'ComponentName' => 'UserItem',
    'Tag' => 'UserItem_Slot12',
    'Fullpath' => '\\views\\home\\userItem.php',
    'relative' => true,
    'TemplatePath' => '\\views\\home\\UserItem_Slot12.html',
    'BuildPath' => '\\_slots\\views\\home\\UserItem_Slot12.php',
    'RenderFunction' => 'RenderUserItem_Slot12',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
  'Layout_Slot13' => 
  array (
    'Name' => 'Layout_Slot13',
    'Namespace' => '',
    'ComponentName' => 'Layout',
    'Tag' => 'Layout_Slot13',
    'Fullpath' => '\\views\\layouts\\layout.php',
    'relative' => true,
    'TemplatePath' => '\\views\\layouts\\Layout_Slot13.html',
    'BuildPath' => '\\_slots\\views\\layouts\\Layout_Slot13.php',
    'RenderFunction' => 'RenderLayout_Slot13',
    'IsComponent' => false,
    'IsSlot' => true,
  ),
));   
}
