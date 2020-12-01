<?php

namespace Viewi\Routing;

include 'RouteItem.php';
include 'Route.php';
include 'Router.php';

echo "Testing router\n";

$r = new Router();
$testCollection = [];
$r->register('get', 'users', 'users-list');
$testCollection[] = [
    'urls' => ['/users', 'users', '/users/anything'],
    'expected' => [true, true, false],
    'action' => 'users-list'
];

$r->register('get', '/posts', 'posts-list');
$testCollection[] = [
    'urls' => ['/posts', 'posts/', '/posts/anything'],
    'expected' => [true, true, false],
    'action' => 'posts-list'
];

$r->register('get', '/user/{userId}', 'user-page');
$testCollection[] = [
    'urls' => ['/user', 'user/23', '/user/myname'],
    'expected' => [false, true, true],
    'params' => [[], ['userId' => '23'], ['userId' => 'myname']],
    'action' => 'user-page'
];
$r->register('get', 'all-posts/{name?}', 'post-by-name');
$testCollection[] = [
    'urls' => ['/all-posts', 'all-posts/23', '/all-posts/myname'],
    'expected' => [true, true, true],
    'params' => [['name' => null], ['name' => '23'], ['name' => 'myname']],
    'action' => 'post-by-name'
];
$r->register('get', 'search/{search}', 'search-page')->where('search', '.*');
$testCollection[] = [
    'urls' => ['/search', '/search/myname', '/search/any/complex/path'],
    'expected' => [false, true, true],
    'params' => [[], ['search' => 'myname'], ['search' => 'any/complex/path']],
    'action' => 'search-page'
];
$r->register('get', '/model/{type}/{id<\\d+>}', 'model-page');
$testCollection[] = [
    'urls' => ['model/product/orange', 'model/product/25', '/model/category/96/'],
    'expected' => [false, true, true],
    'params' => [[], ['type' => 'product', 'id' => '25'], ['type' => 'category', 'id' => '96']],
    'action' => 'model-page'
];
$r->register('get', '/edit/{type}/{id<\\d+>}/{seo?}', 'edit-page-seo');
$testCollection[] = [
    'urls' => ['edit/product/orange', 'edit/product/orange/seo-test', 'edit/product/78', '/edit/category/84/', '/edit/category/84/my-page'],
    'expected' => [false, false, true, true, true],
    'params' => [
        [],
        [],
        ['type' => 'product', 'id' => '78', 'seo' => null],
        ['type' => 'category', 'id' => '84', 'seo' => null],
        ['type' => 'category', 'id' => '84', 'seo' => 'my-page']
    ],
    'action' => 'edit-page-seo'
];
$r->register('get', '/product/type-{type}/{id<\\d+>}/{seo?}', 'product-page');
$testCollection[] = [
    'urls' => [
        '/product/type-fruit/banana',
        'product/type-fruit/banana/seo-test',
        'product/type-fruit/65/banana',
        '/product/type-fruit/54/',
        '/product/fruit/65/my-page'
    ],
    'expected' => [false, false, true, true, false],
    'params' => [
        [],
        [],
        ['type' => 'fruit', 'id' => '65', 'seo' => 'banana'],
        ['type' => 'fruit', 'id' => '54', 'seo' => null],
        []
    ],
    'action' => 'product-page'
];
$r->register('get', '/products/by-{type}type/{query<[A-Za-z]+>?}', 'products-list-by-query');
$r->register('get', 'list-*', 'list-page');
$testCollection[] = [
    'urls' => ['/list', 'list-all', '/list-all/test'],
    'expected' => [false, true, true],
    'action' => 'list-page'
];
$r->register('get', '*', 'page-404');
$testCollection[] = [
    'urls' => ['/any/thing', 'query-search/none'],
    'expected' => [true, true],
    'action' => 'page-404'
];


// ===============================================
function clPassed($text)
{
    print_r("\x1b[42mPASSED => $text\x1b[0m");
}
function clFailed($text)
{
    print_r("\x1b[41m\x1b[1mFAILED => $text\x1b[0m");
}
foreach ($testCollection as $test) {
    $count = count($test['urls']);
    for ($index = 0; $index < $count; $index++) {
        $match = $r->resolve($test['urls'][$index]);
        $routeItem = $match ? $match['route'] : null;
        $params = $match ? $match['params'] : [];
        $action = $routeItem ? $routeItem->action : 'none';
        if (($action === $test['action']) === $test['expected'][$index]) {
            // check$params
            if (isset($test['params']) && $test['expected'][$index]) {
                if (json_encode($test['params'][$index]) === json_encode($params)) {
                    clPassed($test['urls'][$index] . ' -> ' . $action . ':'
                        . json_encode($params) . ' == '
                        . json_encode($test['params'][$index]));
                } else {
                    clFailed($test['urls'][$index] . ' -> ' . $action . ':'
                        . json_encode($params) . ' != '
                        . json_encode($test['params'][$index]));
                }
            } else {
                clPassed($test['urls'][$index] . ' -> ' . $action);
            }
        } else {
            clFailed($test['urls'][$index] . ' -> ' . $action);
        }
        print_r("\n");
    }
}

// performance test
$iterations = 10000;
$time = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $r->resolve('product/type-fruit/banana/seo-test');
}
$total = microtime(true) - $time;
$perSec = $iterations / $total;
echo "Elapsed $total for $iterations iterations; $perSec rps";
