export type TBaseComponent = {
    _props: { [key: string]: any };
    $_callbacks: { [key: string]: Function };
    _refs: { [key: string]: HTMLElement };
    _slots: { [key: string]: any };
    _element?: HTMLElement | null,
    $$r: { [key: string]: Function },
    $$p: TBaseComponent,
    emitEvent: (name: string, event?: any) => void
}

export type TGBaseComponent<T> = TBaseComponent & {
    $$p: TBaseComponent & T
}

export function BaseComponent<T>(this: TGBaseComponent<T>) {
    this._props = {};
    this._refs = {};
    this._slots = {};
    this._element = null;
    this.$_callbacks = {};
    this.emitEvent = function (name: string, event?: any) {
        if (this.$_callbacks && name in this.$_callbacks) {
            this.$_callbacks[name](event);
        }
    }
};