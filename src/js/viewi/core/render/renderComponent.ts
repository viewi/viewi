import { components } from "../../../app/main/components";
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
import { globalScope } from "../di/globalScope";
import { HtmlNodeType } from "../node/htmlNodeType";
import { IRenderable } from "./iRenderable";

export function renderComponent(target: HtmlNodeType, name: string, props?: PropsContext, slots?: Slots, hydrate = false, insert = false, params: { [key: string]: any } = {}): ContextScope {
    if (!(name in componentsMeta.list)) {
        throw new Error(`Component ${name} not found.`);
    }
    if (!(name in components)) {
        throw new Error(`Component ${name} not found.`);
    }
    const info = componentsMeta.list[name];
    const root = info.nodes;
    // 'Reuse' is the concept of reusing same layout(s) to avoid rerendering the whole page and side-effects, including visual.
    // Top level component tag should be reused along with their rendered content, except slots
    // Duplicates are not allowed, otherwise the architecture is wrong and you must reconsider it
    const lastIteration = globalScope.lastIteration;
    const reuse = name in lastIteration;
    if (reuse) {
        // clean up previous page slots
        const slotHolders = lastIteration[name].slots;
        for (let slotName in slotHolders) {
            const anchorNode = slotHolders[slotName];
            while (anchorNode.previousSibling._anchor !== anchorNode._anchor) {
                anchorNode.previousSibling!.remove();
            }
        }
        lastIteration[name].scope.keep = true;
    }
    const instance: BaseComponent<any> & IRenderable = reuse ? lastIteration[name].instance : makeProxy(resolve(name, params, false, props?.scope.lastComponent.instance || props?.scope.instance || null));
    // console.log(name, instance._parent?._name, latestComponent?._name);
    if (!reuse) {
        if (info.hooks && info.hooks.init) {
            (instance as any).init();
        }
    }
    const inlineExpressions = name + '_x';
    if (!reuse && inlineExpressions in components) {
        instance.$$t = components[inlineExpressions];
    }
    const scopeId = props ? ++props.scope.counter : 0;
    // TODO: on reuse - attach scope to a new parent
    const scope: ContextScope = reuse ? lastIteration[name].scope : {
        id: scopeId,
        why: name,
        arguments: [], // props ? [...props.scope.arguments] : [],
        instance: instance,
        main: true,
        map: props ? { ...props.scope.map } : {},
        track: [],
        children: {},
        lastComponent: { instance },//props ? props.scope.lastComponent : null,
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
            let callArguments = [parentInstance];
            if (props.scope.arguments) {
                callArguments = callArguments.concat(props.scope.arguments);
            }
            const attribute = props.attributes[a];
            const attrName = attribute.expression
                ? parentInstance.$$t[attribute.code!].apply(null, callArguments)
                : (attribute.content ?? '');
            if (attrName[0] === '(') {
                const eventName = attrName.substring(1, attrName.length - 1);
                if (attribute.children) {
                    const eventHandler =
                        parentInstance.$$t[
                            attribute.dynamic
                                ? attribute.dynamic.code!
                                : attribute.children[0].code!
                        ].apply(null, callArguments) as EventListener;
                    instance.$_callbacks[eventName] = eventHandler;
                    // console.log('Event', attribute, eventName, eventHandler);
                }
            } else if (attrName[0] === '#') {
                const refName = attrName.substring(1, attrName.length);
                parentInstance._refs[refName] = instance;
                if (refName in parentInstance) {
                    parentInstance[refName] = instance;
                }
            } else {
                const isModel = attrName === 'model';
                let valueContent: any = null;
                let valueSubs = []; // TODO: on backend, pass attribute value subs in attribute
                if (isModel) {
                    const attributeValue = attribute.children![0];
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
                    if (attribute.children?.length === 1 && attribute.children[0].content === 'false') {
                        valueContent = false;
                    }
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
    if (!globalScope.cancel && info.hooks && info.hooks.mounted) {
        (instance as any).mounted();
    }

    // reuse && console.log(`Reusing component: ${name}`);
    if (name in globalScope.located) {
        globalScope.iteration[name] = { instance, scope, slots: {} };
    }
    if (reuse) {
        // console.log('Resue: Rendering slots');
        const slotHolders = lastIteration[name].slots;
        for (let slotName in slotHolders) {
            const anchorNode = slotHolders[slotName];
            if (anchorNode.parentNode && document.body.contains(anchorNode)) { // slot is visible on page
                if (slots && slotName in slots) { // slot has been passed
                    const slot = slots[slotName];
                    if (!slot.node.unpacked) {
                        unpack(slot.node);
                        slot.node.unpacked = true;
                    }
                    render(anchorNode, slot.scope.instance, slot.node.children!, slot.scope, undefined, false, true);
                } else {
                    // TODO: render default slot content
                }
                globalScope.iteration[name].slots[slotName] = anchorNode;
            }
        }
        let componentName: string | false = name;
        while (componentName) {
            const componentInfo = componentsMeta.list[componentName];
            componentName = false;
            const componentRoot = componentInfo.nodes;
            if (componentRoot) {
                const rootChildren = componentRoot.children;
                if (rootChildren) {
                    if (rootChildren[0].type === 'component' && rootChildren[0].content! in lastIteration) {
                        globalScope.iteration[rootChildren[0].content!] = lastIteration[rootChildren[0].content!];
                        componentName = rootChildren[0].content!;
                    }
                }
            }
        }
        return scope;
    }
    // render
    if (info.renderer) {
        instance.render(target, name, scope, props, hydrate, insert, params);
    }

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
            if (rootChildren[0].type === 'component') {
                globalScope.located[rootChildren[0].content!] = true;
            }
            // console.log(target, instance, rootChildren);
            rootChildren[0].first = true;
            render(target, instance, rootChildren, scope, undefined, hydrate, insert);
            // console.log(name, instance, rootChildren);
            // console.log(name, instance);
        }
    }
    if (info.hooks && info.hooks.rendered) {
        setTimeout(function () { (instance as any).rendered(); }, 0);
    }
    return scope;
}