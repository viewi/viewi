export abstract class BaseComponent<T> {
    _props: { [key: string]: any } = {};
    $_callbacks: { [key: string]: Function } = {};
    _refs: { [key: string]: HTMLElement } = {};
    _slots: { [key: string]: any } = {};
    _element: HTMLElement | null = null;
    $$r: Function[] = [];
    $: T;
    _name: string = 'BaseComponent';
    emitEvent(name: string, event?: any) {
        if (this.$_callbacks && name in this.$_callbacks) {
            this.$_callbacks[name](event);
        }
    }
}