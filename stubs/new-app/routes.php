<?php

use Viewi\Routing\Route as ViewiRoute;
use Components\Views\Home\HomePage;
use Components\Views\NotFound\NotFoundPage;

ViewiRoute::get('/', HomePage::class);
ViewiRoute::get('*', NotFoundPage::class);