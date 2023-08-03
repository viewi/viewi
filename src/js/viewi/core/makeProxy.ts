import { TBaseComponent } from "./baseComponent";

export function makeProxy<T>(component: T & TBaseComponent): T & TBaseComponent {
    return new Proxy(component, {
        set(obj, prop: string, value) {
            // console.log(arguments);
            var react = obj[prop] !== value;
            var ret = Reflect.set(obj, prop, value);
            react && (prop in obj.$$r) && obj.$$r[prop]();
            return ret;
        }
    });
}