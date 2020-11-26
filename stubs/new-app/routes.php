<?php

use Components\Views\Counter\Counter;
use Viewi\Routing\Route as ViewiRoute;
use Components\Views\Home\HomePage;
use Components\Views\NotFound\NotFoundPage;
use Components\Views\StatefulCounter\StatefulCounter;
use Components\Views\StatefulTodoApp\StatefulTodoApp;
use Components\Views\TodoApp\TodoApp;

ViewiRoute::get('/', HomePage::class);
ViewiRoute::get('/counter', Counter::class);
ViewiRoute::get('/todo', TodoApp::class);
ViewiRoute::get('/stateful-counter', StatefulCounter::class);
ViewiRoute::get('/stateful-todo', StatefulTodoApp::class);
ViewiRoute::get('*', NotFoundPage::class);
