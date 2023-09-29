import { anchors } from "./core/anchor";
import componentsMeta from "./core/componentsMeta";
import { renderComponent } from "./core/renderComponent";

const Viewi = () => ({
    version: '2.0.1'
});
globalThis.Viewi = Viewi
export { Viewi };

console.log('Viewi entry');

const counterTarget = document.getElementById('counter');

export function renderApp(name: string) {
    renderComponent(counterTarget!, name, undefined, {}, true, false);
    // console.log(anchors);
    // return;
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

// testing Counter
(async () => {
    componentsMeta.list = await (await fetch('/assets/components.json')).json();
    setTimeout(() => renderApp('TestComponent'), 500);
})();