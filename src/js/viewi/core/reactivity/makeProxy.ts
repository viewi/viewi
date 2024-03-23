import { BaseComponent } from "../component/baseComponent";
import { ReserverProps } from "../component/reserverProps";

let reactiveId = 0;

let queue = {};

let timeoutId: number = 0;

function executeQueue() {
    timeoutId = 0;
    const currentQueue = queue;
    queue = {};
    for (let uid in currentQueue) {
        const callbackFunc = currentQueue[uid];
        try {
            callbackFunc[0].apply(null, callbackFunc[1]);
        } catch (err) {
            console.error(err);
        }
    }
}

function schedule(path: string, i: string, callbackFunc: any) {
    queue[path + '-' + i] = callbackFunc;
    if (timeoutId === 0) {
        timeoutId = setTimeout(executeQueue, 0);
    }
}

export type ReactiveProxy = object & { $: ReactiveProxy, $$r?: { [key: string]: [path: string, instance: BaseComponent<any>] } };

export function activateTarget<T>(component: T & BaseComponent<T>, mainPath: string, prop: string, target: any) {
    let val = target[prop];
    if (!Object.getOwnPropertyDescriptor(target, prop)?.set) {
        Object.defineProperty(target, prop, {
            enumerable: true,
            configurable: true,
            get: function () {
                return val;
            },
            set: function (value) {
                const react = val !== value;
                val = value;
                deepProxy(mainPath, component, val);
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
                                schedule(path, i, callbackFunc);
                            }
                        }
                    }
                }
            }
        });
        deepProxy(mainPath, component, val);
    }
}


function deepProxy<T>(prop: string, component: T & BaseComponent<T>, targetObject: ReactiveProxy) {
    if (!(prop in ReserverProps)) {
        if (Array.isArray(targetObject)) {
            // TODO: 
        }
        else if (targetObject !== null && typeof targetObject === 'object' && typeof targetObject !== 'function' && !(targetObject instanceof EventTarget)) {
            if (!('$$r' in targetObject)) {
                Object.defineProperty(targetObject, "$$r", {
                    enumerable: false,
                    writable: true,
                    value: {}
                });
            }
            let keys = Object.keys(targetObject);
            for (let i = 0; i < keys.length; i++) {
                const valueProp = keys[i];
                if (!(valueProp in ReserverProps)) {
                    activateTarget(component, prop, valueProp, targetObject);
                }
            }
            const trackerId = ++reactiveId + '';
            targetObject.$$r![trackerId] = [prop, component];
            component.$$p.push([trackerId, targetObject]);
        }
    }
}

export function defineReactive<T>(component: T & BaseComponent<T>, prop: string) {
    let val = component[prop];
    deepProxy(prop, component, val);
    Object.defineProperty(component, prop, {
        enumerable: true,
        configurable: true,
        get: function () {
            return val;
        },
        set: function (value) {
            const react = val !== value;
            val = value;
            deepProxy(prop, component, val);
            if (react && (prop in component.$$r)) {
                for (let i in component.$$r[prop]) {
                    const callbackFunc = component.$$r[prop][i];
                    schedule(prop, i, callbackFunc);
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