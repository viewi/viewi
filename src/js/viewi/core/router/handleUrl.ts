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
    setTimeout(function () {
        if (locationScope.scrollTo) {
            const toTarget = document.getElementById(locationScope.scrollTo.substring(1));
            toTarget && toTarget.scrollIntoView();
        } else {
            window.scrollTo(0, 0);
        }
    }, 50);
    onUrlUpdate.callback?.();
}

export function handleUrl(href: string, forward: boolean = true, clean: boolean = false) {
    if (href.indexOf('://') !== -1 && href.indexOf(location.origin) !== 0) {
        // external
        location.href = href;
        return;
    }
    globalScope.cancel = true;
    globalScope.cancelIterationId = globalScope.iterationId + 1;
    const urlPath = getPathName(href);
    if (!clean && locationScope.scrollTo && locationScope.skipRender) {
        const toTarget = document.getElementById(locationScope.scrollTo.substring(1));
        toTarget && toTarget.scrollIntoView();
        return;
    }
    const routeItem = componentsMeta.router.resolve(urlPath);
    if (routeItem == null) {
        throw 'Can\'t resolve route for uri: ' + urlPath;
    }
    setTimeout(function () {
        renderApp(routeItem.item.action, routeItem.params, undefined, { func: updateHistory, href, forward });
    }, 0);
}
