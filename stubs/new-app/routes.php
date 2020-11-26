<?php

use Components\Views\AllAtOnce\DemoListPage;
use Components\Views\Home\HomePage;
use Components\Views\NotFound\NotFoundPage;
use Components\Views\Pages\CounterPage;
use Components\Views\Pages\StatefulCounterPage;
use Components\Views\Pages\StatefulTodoAppPage;
use Components\Views\Pages\TodoAppPage;
use Viewi\Routing\Route as ViewiRoute;

ViewiRoute::get('/', HomePage::class);
ViewiRoute::get('/counter', CounterPage::class);
ViewiRoute::get('/todo', TodoAppPage::class);
ViewiRoute::get('/stateful-counter', StatefulCounterPage::class);
ViewiRoute::get('/stateful-todo', StatefulTodoAppPage::class);
ViewiRoute::get('/all-at-once', DemoListPage::class);
ViewiRoute::get('*', NotFoundPage::class);
