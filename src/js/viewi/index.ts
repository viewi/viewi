import { BaseComponent, TBaseComponent, TGBaseComponent } from "./core/baseComponent";
import { makeProxy } from "./core/makeProxy";
import { render } from "./core/render";
import { HomeComponent, THomeComponent } from "./tests/HomeComponent";

const Viewi = () => ({
    version: '2.0.0'
});
globalThis.Viewi = Viewi
export { Viewi };

// test

const homeComponent = (new HomeComponent() as TGBaseComponent<THomeComponent>).$$p;
console.log(homeComponent);
globalThis.homeComponent = homeComponent;

const target = document.getElementById('app');
if (target !== null) {
    render(target, homeComponent);
}
setInterval(() => homeComponent.count++, 1000);



export interface IBaseComponent {
    _name_: string;
    _props: { [key: string]: any };
    $_callbacks: { [key: string]: Function };
    _refs: { [key: string]: HTMLElement };
    _slots: { [key: string]: any };
    _element?: HTMLElement | null,
    $$r: { [key: string]: Function },
    $$p: IBaseComponent | null,
    emitEvent: (name: string, event?: any) => void
};

export interface ICreate<T> {
    create(child?: T): T
}

export interface ITodoComponent extends IBaseComponent {
    text: string;
    items: string[];
    count: number;
    handleSubmit: (event: any) => void;
    increment: () => void;
};

const baseComponent: ICreate<IBaseComponent> = {
    create(child?: IBaseComponent) {
        const base = child || {} as IBaseComponent;
        base._name_ = 'BaseComponent';
        base._props = {};
        base.$$r = {};
        base.$$p = null;
        base._refs = {};
        base._slots = {};
        base._element = null;
        base.$_callbacks = {};
        base.emitEvent = function (name: string, event?: any) {
            if (this.$_callbacks && name in this.$_callbacks) {
                this.$_callbacks[name](event);
            }
        };
        return base;
    }
};


function makeProxy2<T>(component: T & IBaseComponent): T {
    const proxy = new Proxy(component, {
        set(obj, prop: string, value) {
            // console.log(arguments);
            var react = obj[prop] !== value;
            var ret = Reflect.set(obj, prop, value);
            react && (prop in obj.$$r) && obj.$$r[prop]();
            return ret;
        }
    });
    proxy.$$p = proxy;
    return proxy;
}

const todoApp: ICreate<ITodoComponent> = {
    create(child?: ITodoComponent): ITodoComponent {
        let a = 1;
        const base = child || {} as ITodoComponent;
        base._name_ = 'TodoApp';
        baseComponent.create(base);
        const $this: ITodoComponent = makeProxy2<ITodoComponent>(base);
        base.count = 0;
        base.text = '';
        base.items = [];

        base.handleSubmit = function (event) {
            event.preventDefault();
            if ($this.text.length == 0) {
                return;
            }
            $this.items.push($this.text);
            $this.text = '';
        };

        base.increment = function () {
            $this.count++;
            return ++a;
        }
        return $this;
    }
}
const b = todoApp.create();
const c = todoApp.create();

b.$$r['count'] = () => console.log('Count has changed', b.count);

b.increment();
b.increment();
b.increment();
b.increment();
console.log(b.increment(), c.increment());