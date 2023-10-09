import { components } from "../../../app/components";
import { BaseComponent } from "../component/baseComponent";
import { componentsMeta } from "../component/componentsMeta";
import { getComponentModelHandler } from "../reactivity/handlers/getComponentModelHandler";
import { makeProxy } from "../reactivity/makeProxy";
import { PropsContext } from "../lifecycle/propsContext";
import { render } from "./render";
import { resolve } from "../di/resolve";
import { Slots } from "../node/slots";
import { track } from "../reactivity/track";
import { unpack } from "../node/unpack";
import { updateComponentModel } from "../reactivity/handlers/updateComponentModel";
import { updateProp } from "../reactivity/handlers/updateProp";
import { ContextScope } from "../lifecycle/contextScope";

export function renderComponent(target: Node, name: string, props?: PropsContext, slots?: Slots, hydrate = false, insert = false) {
    if (!(name in componentsMeta.list)) {
        throw new Error(`Component ${name} not found.`);
    }
    if (!(name in components)) {
        throw new Error(`Component ${name} not found.`);
    }
    const info = componentsMeta.list[name];
    const root = info.nodes;
    const instance: BaseComponent<any> = makeProxy(resolve(name));
    const inlineExpressions = name + '_x';
    if (inlineExpressions in components) {
        instance.$$t = components[inlineExpressions];
    }
    const scopeId = props ? ++props.scope.counter : 0;
    const scope: ContextScope = {
        id: scopeId,
        arguments: props ? [...props.scope.arguments] : [],
        components: [],
        instance: instance,
        main: true,
        map: props ? { ...props.scope.map } : {},
        track: [],
        children: {},
        counter: 0,
        parent: props ? props.scope : undefined,
        slots: slots
    };
    props && (props.scope.children[scopeId] = scope);
    if (info.refs) {
        scope.refs = info.refs;
    }
    // set props
    if (props && props.attributes) {
        const parentInstance = props.scope.instance;
        for (let a in props.attributes) {
            const attribute = props.attributes[a];
            const attrName = attribute.expression
                ? parentInstance.$$t[attribute.code!](parentInstance) // TODO: arguments
                : (attribute.content ?? '');
            if (attrName[0] === '(') {
                const eventName = attrName.substring(1, attrName.length - 1);
                if (attribute.children) {
                    const eventHandler =
                        parentInstance.$$t[
                            attribute.dynamic
                                ? attribute.dynamic.code!
                                : attribute.children[0].code!
                        ](parentInstance) as EventListener;
                    instance.$_callbacks[eventName] = eventHandler;
                    // console.log('Event', attribute, eventName, eventHandler);
                }
            } else {
                const isModel = attrName === 'model';
                let valueContent: any = null;
                let valueSubs = []; // TODO: on backend, pass attribute value subs in attribute
                if (isModel) {
                    const attributeValue = attribute.children![0];
                    let callArguments = [parentInstance];
                    if (props.scope.arguments) {
                        callArguments = callArguments.concat(props.scope.arguments);
                    }
                    const getterSetter = parentInstance.$$t[attributeValue.code as number].apply(null, callArguments);
                    valueContent = getterSetter[0](parentInstance);
                    instance.$_callbacks[attrName] = getComponentModelHandler(parentInstance, getterSetter[1]);
                    for (let subI in attributeValue.subs!) {
                        const trackingPath = attributeValue.subs[subI];
                        track(parentInstance, trackingPath, props.scope, [updateComponentModel, [instance, attrName, getterSetter[0], parentInstance]]);
                    }
                } else {
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
                    } else {
                        valueContent = true; // empty property conosidered bollean true
                    }
                }
                if (attrName === '_props' && valueContent) {
                    for (let propName in valueContent) {
                        instance[propName] = valueContent[propName];
                        instance._props[propName] = valueContent[propName];
                    }
                } else {
                    instance[attrName] = valueContent;
                    instance._props[attrName] = valueContent;
                }
                // TODO: model
                // track
                if (valueSubs) {
                    for (let subI in valueSubs) {
                        const trackingPath = valueSubs[subI];
                        track(parentInstance, trackingPath, props.scope, [updateProp, [instance, attribute, props]]);
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
        if (rootChildren) {
            // console.log(target, instance, rootChildren);
            rootChildren[0].first = true;
            render(target, instance, rootChildren, scope, undefined, hydrate, insert);
            // console.log(name, instance, rootChildren);
            // console.log(name, instance);
        }
    }
}