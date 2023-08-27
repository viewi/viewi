import { BaseComponent } from "./BaseComponent";

export function makeProxy<T>(component: T & BaseComponent<T>): T {
    const proxy = new Proxy(component, {
        set(obj, prop: string, value) {
            // console.log(arguments);
            var react = obj[prop] !== value;
            var ret = Reflect.set(obj, prop, value);
            react && (prop in obj.$$r) && obj.$$r[prop]();
            return ret;
        }
    });
    component.$ = component;
    return proxy;
}