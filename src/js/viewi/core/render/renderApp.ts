import { anchors } from "../anchor/anchors";
import { globalScope } from "../di/globalScope";
import { renderComponent } from "./renderComponent";

export function renderApp(name: string, params: { [key: string]: any }, target?: Node) {
    console.time('renderApp');
    const hydrate = globalScope.hydrate;
    globalScope.lastIteration = globalScope.iteration;
    globalScope.iteration = {};
    globalScope.located = {};
    renderComponent(target ?? document, name, undefined, {}, hydrate, false);
    globalScope.hydrate = false; // TODO: scope managment function
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
    console.timeEnd('renderApp');
    console.log(globalScope);
}