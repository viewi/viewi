// import { Counter, TodoReducer, CounterReducer } from "../app/components";
import { components } from "../app/components";
import * as functions from "../app/functions";
import { BaseComponent } from "./core/BaseComponent";
import { Anchor } from "./core/anchor";
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
    textNode.nodeValue !== content && (textNode.nodeValue = content);
};

let anchorId = 0;
const anchors: { [key: string]: Anchor } = {};

export function getAnchor(target: HTMLElement & { __aid?: number }): Anchor {
    if (!target.__aid) {
        target.__aid = ++anchorId;
        anchors[target.__aid] = { current: -1, target, invalid: [], added: 0 };
    }
    return anchors[target.__aid];
}

export function hydrateTag(target: HTMLElement, tag: string): HTMLElement {
    const anchor = getAnchor(target);
    const max = target.childNodes.length;
    let end = anchor.current + 3;
    end = end > max ? max : end;
    const invalid: number[] = [];
    for (let i = anchor.current + 1; i < end; i++) {
        const potentialNode = target.childNodes[i];
        if (
            potentialNode.nodeType === 1
            && potentialNode.nodeName.toLowerCase() === tag
        ) {
            anchor.current = i;
            anchor.invalid = anchor.invalid.concat(invalid);
            // console.log('Hydrate match', potentialNode);
            return potentialNode as HTMLElement;
        }
        invalid.push(i);
    }
    anchor.added++;
    console.log('Hydrate not found', tag);
    const element = document.createElement(tag);
    anchor.current++;
    return max > anchor.current
        ? target.insertBefore(element, target.childNodes[anchor.current])
        : target.appendChild(document.createElement(tag));
}

export function hydrateText(target: HTMLElement, instance: BaseComponent<any>, node: TemplateNode): Text {
    const anchor = getAnchor(target);
    const max = target.childNodes.length;
    let end = anchor.current + 3;
    end = end > max ? max : end;
    const invalid: number[] = [];
    for (let i = anchor.current + 1; i < end; i++) {
        const potentialNode = target.childNodes[i];
        if (
            potentialNode.nodeType === 3
        ) {
            anchor.current = i;
            anchor.invalid = anchor.invalid.concat(invalid);
            renderText(instance, node, potentialNode as Text);
            // console.log('Hydrate match', potentialNode);
            return potentialNode as Text;
        }
        invalid.push(i);
    }
    anchor.added++;
    const textNode = document.createTextNode('');
    renderText(instance, node, textNode);
    anchor.current++;
    console.log('Hydrate not found', textNode);
    return max > anchor.current
        ? target.insertBefore(textNode, target.childNodes[anchor.current])
        : target.appendChild(textNode);
}

export function render(target: HTMLElement, instance: BaseComponent<any>, nodes: TemplateNode[]) {
    for (let i in nodes) {
        const node = nodes[i];
        let element: HTMLElement = target;
        let hydrate = true;
        switch (node.type) {
            case <NodeType>'tag':
                {
                    const content = node.expression
                        ? instance.$$t[node.code as number](instance)
                        : (node.content ?? '');
                    element = hydrate
                        ? hydrateTag(target, content)
                        : target.appendChild(document.createElement(content));
                    break;
                }
            case <NodeType>'text':
                {
                    let textNode: Text;
                    if (hydrate) {
                        textNode = hydrateText(target, instance, node);
                    } else {
                        textNode = document.createTextNode('');
                        renderText(instance, node, textNode);
                        target.appendChild(textNode);
                    }
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
                    let valueSubs = []; // TODO: on backend, pass attribute value subs in attribute
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
    renderComponent('Counter');
})();