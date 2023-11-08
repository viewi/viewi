import { anchors } from "../anchor/anchors";
import { componentsMeta } from "../component/componentsMeta";
import { globalScope } from "../di/globalScope";
import { resolve } from "../di/resolve";
import { dispose } from "../lifecycle/dispose";
import { IMiddleware } from "../lifecycle/imiddleware";
import { HtmlNodeType } from "../node/htmlNodeType";
import { renderComponent } from "./renderComponent";

export function renderApp(
    name: string,
    params: { [key: string]: any },
    target?: HtmlNodeType,
    onAccept?: { func: (href: string, forward: boolean) => void, href: string, forward: boolean },
    skipMiddleware?: boolean
) {
    // console.time('renderApp');
    if (!(name in componentsMeta.list)) {
        throw new Error(`Component ${name} not found.`);
    }
    const info = componentsMeta.list[name];
    if (info.lazy) {
        console.warn('Lazy loading not implemented', info);
        return;
    }
    const hydrate = globalScope.hydrate;
    const lastScope = globalScope.rootScope;
    if (onAccept) {
        if (lastScope && info.parent !== globalScope.layout) {
            // new html root, can't render, request from server
            location.href = onAccept.href;
            return;
        }
    }

    if (info.middleware && !skipMiddleware) {
        const total = info.middleware.length;
        let globalAllow = true;
        let current = -1;
        const context = {
            next: function (allow: boolean = true) {
                globalAllow = allow;
                current++;
                if (globalAllow && current < total) {
                    // run next middleware
                    const middleware: IMiddleware = resolve(info.middleware![current]);
                    console.log('Running middleware', middleware);
                    middleware.run(context);
                } else {
                    // render app
                    if (globalAllow) {
                        console.log('Ready to render', globalAllow);
                        renderApp(name, params, target, onAccept, true);
                    } else {
                        console.log('Access denied', globalAllow);
                    }
                }
            }
        };
        context.next(true);
        return;
    }
    if (onAccept) {
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