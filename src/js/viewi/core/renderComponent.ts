import { components } from "../../app/components";
import { BaseComponent } from "./BaseComponent";
import componentsMeta from "./componentsMeta";
import { makeProxy } from "./makeProxy";
import { render } from "./render";
import { unpack } from "./unpack";

export function renderComponent(target: Node, name: string) {
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
        rootChildren && render(target, instance, rootChildren, {
            id: 0,
            arguments: [],
            components: [],
            map: {},
            track: [],
            children: {},
            counter: 0
        });
    }
}