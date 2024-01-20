import { BaseComponent } from "../component/baseComponent";
import { ReserverProps } from "../component/reserverProps";

let reactiveId = 0;

export type ReactiveProxy = object & { $: ReactiveProxy, $$r: { [key: string]: [path: string, instance: BaseComponent<any>] } };

export function activateTarget<T>(component: T & BaseComponent<T>, prop: string, target: any) {
    let val = target[prop];
    if (Array.isArray(val)) {
        // TODO
    }
    else if (val !== null && typeof val === 'object' && typeof val !== 'function') {
        deepProxy(prop, component, val);
    }
    Object.defineProperty(target, prop, {
        enumerable: true,
        configurable: true,
        get: function () {
            return val;
        },
        set: function (value) {
            const react = val !== value;
            val = value;
            if (react) {
                for (let id in target.$$r) {
                    const path = target.$$r[id][0];
                    const component = target.$$r[id][1];
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
        }
    });
}


function deepProxy<T>(prop: string, component: T & BaseComponent<T>, targetObject: ReactiveProxy) {
    if (!(prop in ReserverProps)) {
        let keys = Object.keys(targetObject);
        for (let i = 0; i < keys.length; i++) {
            const valueProp = keys[i];
            if (!(valueProp in ReserverProps)) {
                activateTarget(component, valueProp, targetObject);
            }
            // deepProxy(prop, component, val);
        }
        if (!targetObject.$$r) {
            Object.defineProperty(targetObject, "$$r", {
                enumerable: false,
                writable: true,
                value: {}
            });
        }
        const trackerId = ++reactiveId + '';
        targetObject.$$r[trackerId] = [prop, component];
        component.$$p.push([trackerId, targetObject]);
    }
}

export function defineReactive<T>(component: T & BaseComponent<T>, prop: string) {
    let val = component[prop];
    if (Array.isArray(val)) {
        // TODO: 
    }
    else if (val !== null && typeof val === 'object' && typeof val !== 'function') {
        deepProxy(prop, component, val);
    }
    Object.defineProperty(component, prop, {
        enumerable: true,
        configurable: true,
        get: function () {
            return val;
        },
        set: function (value) {
            const react = val !== value;
            val = value;
            // deepProxy(prop, component, val);
            if (react && (prop in component.$$r)) {
                for (let i in component.$$r[prop]) {
                    const callbackFunc = component.$$r[prop][i];
                    // TODO: schedule queue and react only once
                    callbackFunc[0].apply(null, callbackFunc[1]);
                }
            }
        }
    });
}

export function makeProxy<T>(component: T & BaseComponent<T>): T {
    let keys = Object.keys(component);
    for (let i = 0; i < keys.length; i++) {
        const prop = keys[i];
        if (!(prop in ReserverProps)) {
            defineReactive(component, prop);
        }
    }
    return component;
}