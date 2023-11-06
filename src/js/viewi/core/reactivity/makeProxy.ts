import { BaseComponent } from "../component/baseComponent";
import { ReserverProps } from "../component/reserverProps";

let reactiveId = 0;

export type ReactiveProxy = object & { $: ReactiveProxy, $$r: { [key: string]: [path: string, instance: BaseComponent<any>] } };

export function makeReactive(componentProperty: ReactiveProxy, component: BaseComponent<any>, path: string): ReactiveProxy {
    const targetObject = componentProperty.$ ?? componentProperty;
    if (!targetObject.$) {
        Object.defineProperty(targetObject, "$", {
            enumerable: false,
            writable: true,
            value: targetObject
        });
        Object.defineProperty(targetObject, "$$r", {
            enumerable: false,
            writable: true,
            value: {}
        });
    }
    const proxy = new Proxy(targetObject, {
        set(obj, prop: string, value: any) {
            const react = obj[prop] !== value;
            const ret = Reflect.set(obj, prop, value);
            if (react) {
                for (let id in obj.$$r) {
                    const path = obj.$$r[id][0];
                    const component = obj.$$r[id][1];
                    // const propertyPath = path + '.' + prop;
                    // if (propertyPath in component.$$r) {
                    //     for (let i in component.$$r[propertyPath]) {
                    //         const callbackFunc = component.$$r[propertyPath][i];
                    //         // TODO: schedule queue and react only once
                    //         callbackFunc[0].apply(null, callbackFunc[1]);
                    //     }
                    // }
                    // All root path dependencies should trigger updates, no need for sub path updates
                    if (path in component.$$r) {
                        for (let i in component.$$r[path]) {
                            const callbackFunc = component.$$r[path][i];
                            // TODO: schedule queue and react only once
                            callbackFunc[0].apply(null, callbackFunc[1]);
                        }
                    }
                }
            }
            return ret;
        }
    });
    return proxy;
}

function deepProxy<T>(prop: string, component: T & BaseComponent<T>, value: any) {
    if (!(prop in ReserverProps) && value !== null && typeof value === 'object' && !Array.isArray(value)) {
        const activated = makeReactive(value, component, prop);
        component[prop] = activated;
        const trackerId = ++reactiveId + '';
        activated.$$r[trackerId] = [prop, component];
        component.$$p.push([trackerId, activated]);
    }
}

export function makeProxy<T>(component: T & BaseComponent<T>): T {
    let keys = Object.keys(component);
    for (let i = 0; i < keys.length; i++) {
        const key = keys[i];
        const val = component[key];
        deepProxy(key, component, val);
    }
    const proxy = new Proxy(component, {
        set(obj, prop: string, value) {
            const react = obj[prop] !== value;
            const ret = Reflect.set(obj, prop, value);
            deepProxy(prop, component, value);
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