import { BaseComponent } from "./BaseComponent";

export function makeProxy<T>(component: T & BaseComponent<T>): T {
    const proxy = new Proxy(component, {
        set(obj, prop: string, value) {
            // console.log(arguments);
            var react = obj[prop] !== value;
            var ret = Reflect.set(obj, prop, value);
            if (react && (prop in obj.$$r)) {
                for (let i in obj.$$r[prop]) {
                    const callbackFunc = obj.$$r[prop][i];
                    // TODO: schedule queue and react only once
                    callbackFunc[0].apply(null, callbackFunc[1]);
                }
            }
            return ret;
        }
    });
    component.$ = component;
    return proxy;
}