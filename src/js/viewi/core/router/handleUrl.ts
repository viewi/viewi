import { componentsMeta } from "../component/componentsMeta";
import { globalScope } from "../di/globalScope";
import { renderApp } from "../render/renderApp";
import { locationScope } from "./locationScope";

const getPathName = function (href: string) {
    locationScope.link.href = href;
    return locationScope.link.pathname;
};

export const onUrlUpdate: {
    callback?: Function
} = {};

const updateHistory = function (href: string, forward: boolean = true) {
    if (forward) {
        window.history.pushState({ href: href }, '', href);
    }
    window.scrollTo(0, 0);
    onUrlUpdate.callback?.();
}

export function handleUrl(href: string, forward: boolean = true) {
    globalScope.cancel = true;
    const urlPath = getPathName(href);
    const routeItem = componentsMeta.router.resolve(urlPath);
    if (routeItem == null) {
        throw 'Can\'t resolve route for uri: ' + urlPath;
    }
    setTimeout(function () {
        renderApp(routeItem.item.action, routeItem.params, undefined, { func: updateHistory, href, forward });
    }, 0);
}
