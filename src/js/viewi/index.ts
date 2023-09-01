// import { Counter, TodoReducer, CounterReducer } from "../app/components";
import { components } from "../app/components";
import * as functions from "../app/functions";
import { BaseComponent } from "./core/BaseComponent";
import { makeProxy } from "./core/makeProxy";
import { ComponentMeta, TemplateNode, NodeType } from "./core/node";
import { unpack } from "./core/unpack";
let componentsMeta: ComponentMeta = {};

const Viewi = () => ({
    version: '2.0.1'
});
globalThis.Viewi = Viewi
export { Viewi };

console.log('Viewi entry');

const counterTarget = document.getElementById('counter');

export function renderAttributeValue(instance: BaseComponent<any>, attribute: TemplateNode, element: HTMLElement, attrName) {
    let valueContent: string | null = null;
    if (attribute.children) {
        valueContent = '';
        for (let av in attribute.children) {
            const attributeValue = attribute.children[av];
            valueContent += (attributeValue.expression
                ? instance.$$t[attributeValue.code as number](instance)
                : attributeValue.content) ?? '';
        }
    }
    if (valueContent !== null) {
        element.setAttribute(attrName, valueContent);
    }
};

export function renderText(instance: BaseComponent<any>, node: TemplateNode, textNode: Text) {
    const content = node.expression
        ? instance.$$t[node.code as number](instance)
        : (node.content ?? '');
    textNode.nodeValue = content;
};

export function render(target: HTMLElement, instance: BaseComponent<any>, nodes: TemplateNode[]) {
    for (let i in nodes) {
        const node = nodes[i];
        let element: HTMLElement = target;
        // if (node.expression && node.code) {
        //     node.func = new Function('_component', 'return ' + node.code as string);
        //     console.log('building function', node);
        // }
        const content = node.expression
            ? instance.$$t[node.code as number](instance)
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
                    renderText(instance, node, textNode);
                    if (node.subs) {

                        for (let subI in node.subs) {
                            const trackingPath = node.subs[subI];
                            if (!instance.$$r[trackingPath]) {
                                instance.$$r[trackingPath] = [];
                            }
                            instance.$$r[trackingPath].push([renderText, [instance, node, textNode]]);
                        }
                    }
                    break;
                }
            default: {
                console.log('No implemented', node);
                break;
            }
        }
        if (node.attributes) {
            for (let a in node.attributes) {
                const attribute = node.attributes[a];
                const attrName = attribute.expression
                    ? instance.$$t[attribute.code as number](instance)
                    : (attribute.content ?? '');
                if (attrName[0] === '(') {
                    // event
                    const eventName = attrName.substring(1, attrName.length - 1);
                    if (attribute.children) {
                        const eventHandler = instance.$$t[attribute.children[0].code as number](instance) as EventListener;
                        element.addEventListener(eventName, eventHandler);
                        console.log('Event', attribute, eventName, eventHandler);
                    }
                } else {
                    renderAttributeValue(instance, attribute, element, attrName);
                    let valueSubs = []; // TODO: on backend, pass value subs in attribute
                    if (attribute.children) {
                        for (let av in attribute.children) {
                            const attributeValue = attribute.children[av];
                            if (attributeValue.subs) {
                                valueSubs = valueSubs.concat(attributeValue.subs as never[]);
                            }
                        }
                    }
                    if (valueSubs) {
                        for (let subI in valueSubs) {
                            const trackingPath = valueSubs[subI];
                            if (!instance.$$r[trackingPath]) {
                                instance.$$r[trackingPath] = [];
                            }
                            instance.$$r[trackingPath].push([renderAttributeValue, [instance, attribute, element, attrName]]);
                        }
                    }
                }
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
}

// testing Counter
(async () => {
    componentsMeta = await (await fetch('/assets/components.json')).json();
    renderComponent('Counter');
})();