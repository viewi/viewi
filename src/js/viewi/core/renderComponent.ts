import { components } from "../../app/components";
import { BaseComponent } from "./BaseComponent";
import componentsMeta from "./componentsMeta";
import { ContextScope } from "./contextScope";
import { makeProxy } from "./makeProxy";
import { TemplateNode } from "./node";
import { PropsContext } from "./propsContext";
import { render } from "./render";
import { track } from "./track";
import { unpack } from "./unpack";
import { updateProp } from "./updateProp";

export function renderComponent(target: Node, name: string, scope: ContextScope, props?: PropsContext, hydrate = false, insert = false) {
    if (!(name in componentsMeta.list)) {
        throw new Error(`Component ${name} not found.`);
    }
    if (!(name in components)) {
        throw new Error(`Component ${name} not found.`);
    }
    const root = componentsMeta.list[name].nodes;
    const instance: BaseComponent<any> = makeProxy(new components[name]());
    const inlineExpressions = name + '_x';
    if (inlineExpressions in components) {
        instance.$$t = components[inlineExpressions];
    }
    // set props
    if (props && props.attributes) {
        const parentInstance = props.instance;
        for (let a in props.attributes) {
            const attribute = props.attributes[a];
            const attrName = attribute.expression
                ? parentInstance.$$t[attribute.code!](parentInstance) // TODO: arguments
                : (attribute.content ?? '');
            if (attrName[0] === '(') {
                // TODO: event
            } else {
                let valueContent: any = null;
                let valueSubs = []; // TODO: on backend, pass attribute value subs in attribute
                if (attribute.children) {
                    for (let av = 0; av < attribute.children.length; av++) {
                        const attributeValue = attribute.children[av];
                        let callArguments = [parentInstance];
                        if (props.scope.arguments) {
                            callArguments = callArguments.concat(props.scope.arguments);
                        }
                        const childContent = attributeValue.expression
                            ? parentInstance.$$t[attributeValue.code as number].apply(null, callArguments)
                            : (attributeValue.content ?? '');
                        valueContent = av === 0 ? childContent : valueContent + (childContent ?? '');
                        if (attributeValue.subs) {
                            valueSubs = valueSubs.concat(attributeValue.subs as never[]);
                        }
                    }
                }
                instance[attrName] = valueContent;
                instance._props[attrName] = valueContent;
                // TODO: _props, model
                // track
                if (valueSubs) {
                    for (let subI in valueSubs) {
                        const trackingPath = valueSubs[subI];
                        track(parentInstance, trackingPath, scope, [updateProp, [instance, attribute, props]]);
                    }
                }
            }
        }
    }
    // render
    if (
        target
        && root
    ) {
        if (!root.unpacked) {
            unpack(root);
            root.unpacked = true;
        }
        const rootChildren = root.children;
        // console.log(counterTarget, instance, rootChildren);
        rootChildren && render(target, instance, rootChildren, scope, undefined, hydrate, insert);
    }
}

export function isComponent(name: string) {
    return (name in componentsMeta.list);
}