import { componentsMeta } from "../component/componentsMeta";
import { renderApp } from "../render/renderApp";
import { locationScope } from "./locationScope";

const getPathName = function (href: string) {
    locationScope.link.href = href;
    return locationScope.link.pathname;
};

const updateHistory = function (href: string, forward: boolean = true) {
    if (forward) {
        window.history.pushState({ href: href }, '', href);
    }
}

export function handleUrl(href: string, forward: boolean = true) {
    const urlPath = getPathName(href);
    const routeItem = componentsMeta.router.resolve(urlPath);
    if (routeItem == null) {
        throw 'Can\'t resolve route for uri: ' + urlPath;
    }
    renderApp(routeItem.item.action, routeItem.params, undefined, { func: updateHistory, href, forward });
}