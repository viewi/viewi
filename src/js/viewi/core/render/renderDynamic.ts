import { BaseComponent } from "../component/baseComponent";
import { TemplateNode } from "../node/templateNode";
import { PropsContext } from "../lifecycle/propsContext";
import { render } from "./render";
import { renderAttributeValue } from "./renderAttributeValue";
import { renderComponent } from "./renderComponent";
import { track } from "../reactivity/track";
import { isComponent } from "../component/isComponent";
import { TextAnchor } from "../anchor/textAnchor";
import { ContextScope } from "../lifecycle/contextScope";
import { dispose } from "../lifecycle/dispose";

export function renderDynamic(instance: BaseComponent<any>, node: TemplateNode, scopeContainer: { scope: ContextScope, anchorNode: TextAnchor }) {
    const content = node.expression
        ? instance.$$t[node.code as number](instance)
        : (node.content ?? '');
    const componentTag = node.type === "component"
        || (node.expression && isComponent(content));
    const anchorNode = scopeContainer.anchorNode;
    const scope = scopeContainer.scope.parent!;
    dispose(scopeContainer.scope);
    while (anchorNode.previousSibling._anchor !== anchorNode._anchor) {
        anchorNode.previousSibling!.remove();
    }
    const scopeId = ++scope.counter;
    const nextScope: ContextScope = {
        id: scopeId,
        why: 'dynamic',
        arguments: [...scope.arguments],
        map: { ...scope.map },
        track: [],
        instance: instance,
        lastComponent: scope.lastComponent,
        parent: scope,
        children: {},
        counter: 0,
        slots: scope.slots
    };
    if (scope.refs) {
        nextScope.refs = scope.refs;
    }
    scopeContainer.scope = nextScope;
    scope.children[scopeId] = nextScope;
    // component
    if (componentTag) {
        const slots = {};
        if (node.slots) {
            const scopeId = ++nextScope!.counter;
            const slotScope: ContextScope= {
                id: scopeId,
                why: 'slot',
                arguments: [...scope.arguments],
                map: { ...scope.map },
                track: [],
                instance: instance,
                lastComponent: scope.lastComponent,
                parent: nextScope,
                children: {},
                counter: 0,
                slots: scope.slots
            };
            for (let slotName in node.slots) {
                slots[slotName] = {
                    node: node.slots[slotName],
                    scope: slotScope
                };
            }
        }
        renderComponent(anchorNode, content, <PropsContext>{ attributes: node.attributes, scope: scope, instance: instance }, slots, false, true);
        return;
    } else {
        const element = anchorNode.parentElement!.insertBefore(document.createElement(content), anchorNode);

        if (node.attributes) {
            for (let a in node.attributes) {
                const attribute = node.attributes[a];
                const attrName = attribute.expression
                    ? instance.$$t[attribute.code!](instance)
                    : (attribute.content ?? '');
                if (attrName[0] === '(') {
                    // event
                    const eventName = attrName.substring(1, attrName.length - 1);
                    if (attribute.children) {
                        const eventHandler =
                            instance.$$t[
                                attribute.dynamic
                                    ? attribute.dynamic.code!
                                    : attribute.children[0].code!
                            ](instance) as EventListener;
                        element.addEventListener(eventName, eventHandler);
                        // console.log('Event', attribute, eventName, eventHandler);
                    }
                } else {
                    renderAttributeValue(instance, attribute, <HTMLElement>element, attrName, nextScope);
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
                            track(instance, trackingPath, nextScope, [renderAttributeValue, [instance, attribute, element, attrName, nextScope]]);
                        }
                    }
                }
            }
        }
        if (node.children) {
            render(element, instance, node.children, nextScope, undefined, false, false);
        }
    }
}