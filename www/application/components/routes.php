<?php

use Viewi\Routing\Route as ViewiRoute;

include 'application/components/views/home/home.php';
include 'application/components/views/posts/post.php';
include 'application/components/views/notfound/notfound.php';

ViewiRoute::get('/', HomePage::class);
ViewiRoute::get('/post/{postId}', PostPage::class);
ViewiRoute::get('*', NotFoundComponent::class);
