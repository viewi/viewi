import { ReactiveProxy } from "../reactivity/makeProxy";

export abstract class BaseComponent<T> {
    __id: string = '';
    _props: { [key: string]: any } = {};
    $_callbacks: { [key: string]: Function } = {};
    _refs: { [key: string]: Node | BaseComponent<any> } = {};
    _slots: { [key: string]: any } = {};
    _element: Node | null = null;
    $$t: Function[] = []; // template inline expressions
    $$r: { [key: string]: { [key: string]: [Function, any[]] } } = {}; // reactivity callbacks
    $$p: [trackerId: string, activated: ReactiveProxy][] = []; // shared reactivity track ids
    $: T;
    _provides: Object;
    _parent: null | BaseComponent<any> = null;
    _name: string = 'BaseComponent';

    emitEvent(name: string, event?: any) {
        if (name in this.$_callbacks) {
            this.$_callbacks[name](event);
        }
    }

    provide(key: string, value: any): void {
        if (this._provides === this._parent?._provides) {
            this._provides = Object.create(this._provides);
        }
        this._provides[key] = value;
    }

    inject(key: string): any {
        return this._provides[key] || null;
    }
}