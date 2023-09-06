import { TemplateNode } from "./node";

export abstract class BaseComponent<T> {
    _props: { [key: string]: any } = {};
    $_callbacks: { [key: string]: Function } = {};
    _refs: { [key: string]: Node } = {};
    _slots: { [key: string]: any } = {};
    _element: Node | null = null;
    $$t: Function[] = []; // template inline expressions
    $$r: { [key: string]: [Function, any[]][] } = {}; // reactivity callbacks
    $: T;
    _name: string = 'BaseComponent';
    emitEvent(name: string, event?: any) {
        if (this.$_callbacks && name in this.$_callbacks) {
            this.$_callbacks[name](event);
        }
    }
}