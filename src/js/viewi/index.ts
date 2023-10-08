import { anchors } from "./core/anchor/anchors";
import { componentsMeta } from "./core/component/componentsMeta";
import { renderComponent } from "./core/render/renderComponent";

const Viewi = () => ({
    version: '2.0.1'
});
globalThis.Viewi = Viewi
export { Viewi };

console.log('Viewi entry');

const counterTarget = document.getElementById('counter');

export function renderApp(name: string) {
    console.time('renderApp');
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
    // console.timeEnd('renderApp');
    // console.timeLog('renderApp');
    console.timeEnd('renderApp');
}

// testing Counter
(async () => {
    const data = await (await fetch('/assets/components.json')).json();
    componentsMeta.list = data;
    const booleanArray = (<{
        _meta: { boolean: string }
    }>data)._meta['boolean'].split(',');
    for (let i = 0; i < booleanArray.length; i++) {
        componentsMeta.booleanAttributes[booleanArray[i]] = true;
    }
    setTimeout(() => renderApp('TestComponent'), 500);
})();