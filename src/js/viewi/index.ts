// import { Counter, TodoReducer, CounterReducer } from "../app/components";
import { components } from "../app/components";
import * as functions from "../app/functions";
import { BaseComponent } from "./core/BaseComponent";
import { anchors } from "./core/anchor";
import { makeProxy } from "./core/makeProxy";
import { ComponentMeta } from "./core/node";
import { render } from "./core/render";
import { unpack } from "./core/unpack";
let componentsMeta: ComponentMeta = {};

const Viewi = () => ({
    version: '2.0.1'
});
globalThis.Viewi = Viewi
export { Viewi };

console.log('Viewi entry');

const counterTarget = document.getElementById('counter');

export function renderComponent(name: string) {
    if (!(name in componentsMeta)) {
        throw new Error(`Component ${name} not found.`);
    }
    if (!(name in components)) {
        throw new Error(`Component ${name} not found.`);
    }
    const root = componentsMeta[name].nodes;
    const instance: BaseComponent<any> = makeProxy(new components[name]());
    const inlineExpressions = name + '_x';
    if (inlineExpressions in components) {
        instance.$$t = components[inlineExpressions];
    }
    if (
        counterTarget
        && root
    ) {
        if (!root.unpacked) {
            unpack(root);
            root.unpacked = true;
        }
        const rootChildren = root.children;
        console.log(counterTarget, instance, rootChildren);
        rootChildren && render(counterTarget, instance, rootChildren);
    }
    console.log(anchors);
    for (let a in anchors) {
        const anchor = anchors[a];
        // clean up not matched
        for (let i = anchor.invalid.length - 1; i >= 0; i--) {
            anchor.target.childNodes[anchor.invalid[i]].remove();
        }
        // clean up what's left
        for (let i = anchor.current + 1; i < anchor.target.childNodes.length; i++) {
            anchor.target.childNodes[i].remove();
        }
    }
}

// testing Counter
(async () => {
    componentsMeta = await (await fetch('/assets/components.json')).json();
    setTimeout(() => renderComponent('TestComponent'), 500);
})();