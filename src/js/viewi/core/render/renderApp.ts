import { anchors } from "../anchor/anchors";
import { componentsMeta } from "../component/componentsMeta";
import { globalScope } from "../di/globalScope";
import { dispose } from "../lifecycle/dispose";
import { HtmlNodeType } from "../node/htmlNodeType";
import { renderComponent } from "./renderComponent";

export function renderApp(
    name: string,
    params: { [key: string]: any },
    target?: HtmlNodeType,
    onAccept?: { func: (href: string, forward: boolean) => void, href: string, forward: boolean }
) {
    // console.time('renderApp');
    if (!(name in componentsMeta.list)) {
        throw new Error(`Component ${name} not found.`);
    }
    const info = componentsMeta.list[name];
    const hydrate = globalScope.hydrate;
    const lastScope = globalScope.rootScope;
    if (onAccept) {
        if (lastScope && info.parent !== globalScope.layout) {
            // new html root, can't render, request from server
            location.href = onAccept.href;
            return;
        }
        onAccept.func(onAccept.href, onAccept.forward);
    }
    globalScope.layout = info.parent!;
    globalScope.lastIteration = globalScope.iteration;
    globalScope.iteration = {};
    globalScope.scopedContainer = {};
    globalScope.located = {};
    globalScope.rootScope = renderComponent(target ?? document, name, undefined, {}, hydrate, false, params);
    globalScope.hydrate = false; // TODO: scope managment function
    for (let name in globalScope.lastIteration) {
        if (!(name in globalScope.iteration)) {
            globalScope.lastIteration[name].scope.keep = false;
        }
    }
    lastScope && dispose(lastScope);
    // console.log(anchors);
    // return;

    // Clean up unhydrated content
    if (hydrate) {
        for (let a in anchors) {
            const anchor = anchors[a];
            // clean up what's left
            for (let i = anchor.target.childNodes.length - 1; i >= anchor.current + 1; i--) {
                anchor.target.childNodes[i].remove();
            }
            // clean up not matched
            for (let i = anchor.invalid.length - 1; i >= 0; i--) {
                anchor.target.childNodes[anchor.invalid[i]].remove();
            }
        }
    }
    // console.timeEnd('renderApp');
    // console.timeLog('renderApp');
    // console.timeEnd('renderApp');
    // console.log(globalScope);
}