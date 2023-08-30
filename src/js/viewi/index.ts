// import { Counter, TodoReducer, CounterReducer } from "../app/components";
import { components } from "../app/components";
import * as functions from "../app/functions";
import { BaseComponent } from "./core/BaseComponent";
import { makeProxy } from "./core/makeProxy";
import { ComponentMeta, Node, NodeType } from "./core/node";
import { unpack } from "./core/unpack";
let componentsMeta: ComponentMeta = {};

const Viewi = () => ({
    version: '2.0.1'
});
globalThis.Viewi = Viewi
export { Viewi };

console.log('Viewi entry');

const counterTarget = document.getElementById('counter');

export function render(target: HTMLElement, instance: BaseComponent<any>, nodes: Node[]) {
    for (let i in nodes) {
        const node = nodes[i];
        if (!node.unpacked) {
            unpack(node);
            node.unpacked = true;
        }
        let element: HTMLElement = target;
        // if (node.expression && node.code) {
        //     node.func = new Function('_component', 'return ' + node.code as string);
        //     console.log('building function', node);
        // }
        let content = node.expression
            ? instance.$$r[node.code as number](instance)
            : (node.content ?? '');
        switch (node.type) {
            case <NodeType>'tag':
                {
                    element = document.createElement(content);
                    target.appendChild(element);
                    console.log('tag', node);
                    break;
                }
            case <NodeType>'text':
                {
                    const textNode: Text = document.createTextNode(content);
                    target.appendChild(textNode);
                    console.log('text', node);
                    break;
                }
            default: {
                break;
            }
        }
        if (node.children) {
            render(element, instance, node.children);
        }
    }
}

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
        instance.$$r = components[inlineExpressions];
    }
    if (
        counterTarget
        && root
    ) {
        const rootChildren = root.h;
        console.log(counterTarget, instance, rootChildren);
        rootChildren && render(counterTarget, instance, rootChildren);
    }
}

// testing Counter
(async () => {
    componentsMeta = await (await fetch('/assets/components.json')).json();
    renderComponent('Counter');
})();