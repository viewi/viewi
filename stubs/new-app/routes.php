<?php

use Components\Views\Home\HomePage;
use Components\Views\NotFound\NotFoundPage;
use Viewi\Routing\Route as ViewiRoute;

ViewiRoute::get('/', HomePage::class);
ViewiRoute::get('*', NotFoundPage::class);
