import { componentsMeta } from "../component/componentsMeta";
import { renderApp } from "../render/renderApp";

const htmlElementA = document.createElement('a');

const getPathName = function (href: string) {
    htmlElementA.href = href;
    return htmlElementA.pathname;
};

export function handleUrl(href: string) {
    const urlPath = getPathName(href);
    const routeItem = componentsMeta.router.resolve(urlPath);
    if (routeItem == null) {
        throw 'Can\'t resolve route for uri: ' + urlPath;
    }
    renderApp(routeItem.item.action, routeItem.params);
}