import { RouteItem } from "./routeItem";
import { RouteRecord } from "./routeRecord";

export class Router {
    routes: RouteItem[];
    trimExpr: RegExp = /^\/|\/$/g;

    setRoutes(routeList: RouteItem[]) {
        this.routes = routeList;
    };

    getRoutes(): RouteItem[] {
        return this.routes;
    };

    register(
        method: string,
        url: string,
        action: string,
        defaults: { [key: string]: any } | null = null,
        wheres?: { [key: string]: any }
    ): RouteItem {
        const item = new RouteItem(
            method.toLowerCase(),
            url,
            action,
            defaults,
            wheres
        );
        this.routes.push(item);
        return item;
    };

    get(url: string, action: string): RouteItem {
        return this.register('get', url, action);
    };

    resolve(url: string): RouteRecord | null {
        url = url.replace(this.trimExpr, '');
        const parts = url.split('/');
        for (let k in this.routes) {
            const params = {};
            let valid = true;
            const item = this.routes[k];
            const targetUrl = item.url.replace(this.trimExpr, '');
            const targetParts = targetUrl.split('/');
            let pi = 0;
            let skipAll = false;
            for (pi; pi < targetParts.length; pi++) {
                const urlExpr = targetParts[pi];
                const hasWildCard = urlExpr.indexOf('*') !== -1;
                if (hasWildCard) {
                    const beginning = urlExpr.slice(0, -1);
                    if (!beginning || parts[pi].indexOf(beginning) === 0) {
                        skipAll = true;
                        break;
                    }
                }
                const hasParams = urlExpr.indexOf('{') !== -1;
                if (urlExpr !== parts[pi] && !hasParams) {
                    valid = false;
                    break;
                }
                if (hasParams) {
                    // has {***} parameter
                    const bracketParts = urlExpr.split(/[{}]+/);
                    // console.log(urlExpr, bracketParts);
                    let paramName = bracketParts[1];
                    if (paramName[paramName.length - 1] === '?') {
                        // optional
                        paramName = paramName.slice(0, -1);
                    } else if (pi >= parts.length) {
                        valid = false;
                        break;
                    }
                    if (paramName.indexOf('<') !== -1) { // has <regex>
                        const matches = /<([^>]+)>/.exec(paramName);
                        if (matches) {
                            paramName = paramName.replace(/<([^>]+)>/g, '');
                            item.wheres[paramName] = matches[1];
                        }
                    }
                    if (item.wheres[paramName]) {
                        const regex = new RegExp(item.wheres[paramName], 'g');
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
                    let paramValue = pi < parts.length ? parts[pi] : null;
                    if (paramValue && bracketParts[0]) {
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
}