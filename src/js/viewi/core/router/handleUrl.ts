import { componentsMeta } from "../component/componentsMeta";
import { renderApp } from "../render/renderApp";
import { locationScope } from "./locationScope";

const getPathName = function (href: string) {
    locationScope.link.href = href;
    return locationScope.link.pathname;
};

export function handleUrl(href: string, forward: boolean = true) {
    const urlPath = getPathName(href);
    const routeItem = componentsMeta.router.resolve(urlPath);
    if (routeItem == null) {
        throw 'Can\'t resolve route for uri: ' + urlPath;
    }
    renderApp(routeItem.item.action, routeItem.params);
    // TODO: push state only if app does not redirect or opens location itself
    if (forward) {
        window.history.pushState({ href: href }, '', href);
    }
}