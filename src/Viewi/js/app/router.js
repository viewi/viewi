var RouteItem = function (method, url, action, wheres) {
    this.method = method;
    this.url = url;
    this.action = action;
    this.wheres = {};
    if (wheres) {
        this.wheres = wheres;
    }
    this.where = function (wheresOrName, expr) {
        if (wheresOrName !== null && typeof wheresOrName === 'object') {
            this.wheres = Object.assign(this.where, wheresOrName);
        } else if (expr) {
            this.wheres[wheresOrName] = expr;
        }
        return this;
    };
}

var Router = function () {
    var routes = [];
    var trimExpr = /^\/|\/$/g;
    this.setRoutes = function (routeList) {
        routes = routeList;
    };

    this.register = function (method, url, action) {
        var item = new RouteItem(
            method.toLowerCase(),
            url,
            action
        );
        routes.push(item);
        return item;
    };

    this.get = function (url, action) {
        return this.register('get', url, action);
    };

    this.resolve = function (url) {
        url = url.replace(trimExpr, '');
        var parts = url.split('/');
        for (var k in routes) {
            var params = {};
            var valid = true;
            var item = routes[k];
            var targetUrl = item.url.replace(trimExpr, '');
            var targetParts = targetUrl.split('/');
            var pi = 0;
            var skipAll = false;
            for (pi; pi < targetParts.length; pi++) {
                var urlExpr = targetParts[pi];
                var hasWildCard = urlExpr.indexOf('*') !== -1;
                if (hasWildCard) {
                    var beginning = urlExpr.slice(0, -1);
                    if (!beginning || parts[pi].indexOf(beginning) === 0) {
                        skipAll = true;
                        break;
                    }
                }
                var hasParams = urlExpr.indexOf('{') !== -1;
                if (
                    urlExpr !== parts[pi] && !hasParams
                ) {
                    valid = false;
                    break;
                }
                if (hasParams) {
                    // has {***} parameter
                    var bracketParts = urlExpr.split(/[{}]+/);
                    // console.log(urlExpr, bracketParts);
                    var paramName = bracketParts[1];
                    if (paramName[paramName.length - 1] === '?') {
                        // optional
                        paramName = paramName.slice(0, -1);
                    } else if (pi >= parts.length) {
                        valid = false;
                        break;
                    }
                    if (paramName.indexOf('<') !== -1) { // has <regex>
                        var matches = /<([^>]+)>/.exec(paramName);
                        paramName = paramName.replace(/<([^>]+)>/g, '');
                        item.wheres[paramName] = matches[1];
                    }
                    if (item.wheres[paramName]) {
                        var regex = new RegExp(item.wheres[paramName], 'g');
                        if (!regex.test(parts[pi])) {
                            valid = false;
                            break;
                        }
                        regex.lastIndex = 0;
                        // test for "/"
                        if (regex.test('/')) { // skip to the end
                            skipAll = true;
                        }
                    }
                    var paramValue = pi < parts.length ? parts[pi] : null;
                    if (bracketParts[0]) {
                        if (paramValue.indexOf(bracketParts[0]) !== 0) {
                            valid = false;
                            break;
                        } else {
                            paramValue = paramValue.slice(bracketParts[0].length);
                        }
                    }
                    params[paramName] = paramValue;
                    if (skipAll) {
                        params[paramName] = parts.slice(pi).join('/');
                        break;
                    }
                }
            }
            if (pi < parts.length && !skipAll) {
                valid = false;
            }
            if (valid) {
                return { item: item, params: params };
            }
        }
        return null;
    };

    // do we need post,put,delete,patch,options on front ??
}
