import { BaseComponent } from "./BaseComponent";
import { hydrateTag } from "./hydrateTag";
import { hydrateText } from "./hydrateText";
import { NodeType, TemplateNode } from "./node";
import { renderAttributeValue } from "./renderAttributeValue";
import { renderText } from "./renderText";

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
                        const eventHandler =
                            instance.$$t[
                                attribute.dynamic
                                    ? attribute.dynamic.code as number
                                    : attribute.children[0].code as number
                            ](instance) as EventListener;
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