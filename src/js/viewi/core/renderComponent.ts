import { components } from "../../app/components";
import { BaseComponent } from "./BaseComponent";
import componentsMeta from "./componentsMeta";
import { ContextScope } from "./contextScope";
import { makeProxy } from "./makeProxy";
import { PropsContext } from "./propsContext";
import { render } from "./render";
import { Slots } from "./slots";
import { track } from "./track";
import { unpack } from "./unpack";
import { updateProp } from "./updateProp";

type DIContainer = { [key: string]: any };

const scopedContainer: DIContainer = {}; // TODO: dispose on route change, TODO: expose dispose to public use
const singletonContainer: DIContainer = {};
let nextInstanceId = 0;

export function resolve(name: string, params: any[] = []) {
    const info = componentsMeta.list[name];
    let instance: any = null;
    let container: boolean | DIContainer = false;
    if (info.di === "Singleton") {
        container = singletonContainer;
    } else if (info.di === "Scoped") {
        container = scopedContainer;
    }
    if (container && (name in container)) {
        // console.log('Returning from cache', name, container[name]);
        return container[name];
    }
    if (!info.dependencies) {
        instance = new components[name]();
    } else {
        const constructArguments: any[] = [];
        for (let i in info.dependencies) {
            const dependency = info.dependencies[i];
            var argument: any = null; // d.null
            if (params && (dependency.argName in params)) {
                argument = params[dependency.argName];
            }
            else if (dependency.default) {
                argument = dependency.default; // TODO: copy object or array
            } else if (dependency.null) {
                argument = null;
            } else if (dependency.builtIn) {
                argument = dependency.name === 'string' ? '' : 0;
            } else {
                argument = resolve(dependency.name);
            }
            constructArguments.push(argument);
        }
        instance = new components[name](...constructArguments);
    }
    if (info.base) {
        (<BaseComponent<any>>instance).__id = ++nextInstanceId + '';
    }
    if (container) {
        container[name] = instance;
    }
    return instance;
}

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
                } else {
                    valueContent = true; // empty property conosidered bollean true
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

export function isComponent(name: string) {
    return (name in componentsMeta.list);
}