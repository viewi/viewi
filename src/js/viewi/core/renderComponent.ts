import { components } from "../../app/components";
import { BaseComponent } from "./BaseComponent";
import componentsMeta from "./componentsMeta";
import { ContextScope } from "./contextScope";
import { makeProxy } from "./makeProxy";
import { TemplateNode } from "./node";
import { render } from "./render";
import { unpack } from "./unpack";

export function renderComponent(target: Node, name: string, scope: ContextScope, hydrate = false, insert = false) {
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