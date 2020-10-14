// testing
var cl = console.log;
cl('testing');
var r = new Router();

var testCollection = [];

r.get('users', 'users-list');
testCollection.push({
    urls: ['/users', 'users', '/users/anything'],
    expected: [true, true, false],
    action: 'users-list'
});
r.get('/posts', 'posts-list');
testCollection.push({
    urls: ['/posts', 'posts/', '/posts/anything'],
    expected: [true, true, false],
    action: 'posts-list'
});
r.get('/user/{userId}', 'user-page');
testCollection.push({
    urls: ['/user', 'user/23', '/user/myname'],
    expected: [false, true, true],
    params: [{}, { userId: '23' }, { userId: 'myname' }],
    action: 'user-page'
});
r.get('all-posts/{name?}', 'post-by-name');
testCollection.push({
    urls: ['/all-posts', 'all-posts/23', '/all-posts/myname'],
    expected: [true, true, true],
    params: [{}, { name: '23' }, { name: 'myname' }],
    action: 'post-by-name'
});
r.get('search/{search}', 'search-page').where('search', '.*');
testCollection.push({
    urls: ['/search', '/search/myname', '/search/any/complex/path'],
    expected: [false, true, true],
    params: [{}, { search: 'myname' }, { search: 'any/complex/path' }],
    action: 'search-page'
});
r.get('/model/{type}/{id<\\d+>}', 'model-page');
testCollection.push({
    urls: ['model/product/orange', 'model/product/25', '/model/category/96/'],
    expected: [false, true, true],
    params: [{}, { type: 'product', id: '25' }, { type: 'category', id: '96' }],
    action: 'model-page'
});
r.get('/edit/{type}/{id<\\d+>}/{seo?}', 'edit-page-seo');
testCollection.push({
    urls: ['edit/product/orange', 'edit/product/orange/seo-test', 'edit/product/78', '/edit/category/84/', '/edit/category/84/my-page'],
    expected: [false, false, true, true, true],
    params: [
        {},
        {},
        { type: 'product', id: '78' },
        { type: 'category', id: '84' },
        { type: 'category', id: '84', seo: 'my-page' }
    ],
    action: 'edit-page-seo'
});
r.get('/product/type-{type}/{id<\\d+>}/{seo?}', 'product-page');
testCollection.push({
    urls: [
        '/product/type-fruit/banana',
        'product/type-fruit/banana/seo-test',
        'product/type-fruit/65/banana',
        '/product/type-fruit/54/',
        '/product/fruit/65/my-page'
    ],
    expected: [false, false, true, true, false],
    params: [
        {},
        {},
        { type: 'fruit', id: '65', seo: 'banana' },
        { type: 'fruit', id: '54' },
        {}
    ],
    action: 'product-page'
});
r.get('/products/by-{type}type/{query<[A-Za-z]+>?}', 'products-list-by-query');
r.get('list-*', 'list-page');
testCollection.push({
    urls: ['/list', 'list-all', '/list-all/test'],
    expected: [false, true, true],
    action: 'list-page'
});
r.get('*', 'page-404');
testCollection.push({
    urls: ['/any/thing', 'query-search/none'],
    expected: [true, true],
    action: 'page-404'
});

var clPassed = function (text) {
    cl("\x1b[42m" + 'PASSED: ' + text + "\x1b[0m");
}
var clFailed = function (text) {
    cl("\x1b[41m\x1b[1m" + 'FAILED: ' + text + "\x1b[0m");
}
for (var i in testCollection) {
    var test = testCollection[i];
    for (var index = 0; index < test.urls.length; index++) {
        var match = r.resolve(test.urls[index]);
        var routeItem = match ? match.item : null;
        var params = match ? match.params : {};
        var action = routeItem ? routeItem.action : 'none';
        if ((action === test.action) === test.expected[index]) {
            // check params
            if (test.params && test.expected[index]) {
                if (JSON.stringify(test.params[index]) === JSON.stringify(params)) {
                    clPassed(test.urls[index] + ' -> ' + action + ':'
                        + JSON.stringify(params) + ' == '
                        + JSON.stringify(test.params[index])
                    );
                } else {
                    clFailed(test.urls[index] + ' -> ' + action + ':'
                        + JSON.stringify(params) + ' != '
                        + JSON.stringify(test.params[index])
                    );
                }
            } else {
                clPassed(test.urls[index] + ' -> ' + action);
            }
        } else {
            clFailed(test.urls[index] + ' -> ' + action);
        }
    }
}