import { components } from "../../../app/main/components";
import { BaseComponent } from "../component/baseComponent";
import { componentsMeta } from "../component/componentsMeta";
import { getScopeState } from "../lifecycle/scopeState";
import { DIContainer } from "./diContainer";
import { factoryContainer } from "./factory";
import { globalScope } from "./globalScope";
import { ScopeType } from "./scopeType";

const singletonContainer: DIContainer = {};
let nextInstanceId = 0;
const rootProvides = {};

export function resolve(name: string, params: { [key: string]: any } = {}, canBeNull: boolean = false, parent: BaseComponent<any> | null = null) {
    if (!(name in componentsMeta.list)) {
        if (canBeNull) {
            return null;
        }
        throw new Error("Can't resolve " + name);
    }
    const info = componentsMeta.list[name];
    let instance: any = null;
    let container: boolean | DIContainer = false;
    if (info.di === "SINGLETON") {
        container = singletonContainer;
    } else if (info.di === "SCOPED") {
        container = globalScope.scopedContainer;
    }
    if (container && (name in container)) {
        // console.log('Returning from cache', name, container[name]);
        return container[name];
    }
    const toProvide = {};
    if (info.custom) {
        instance = factoryContainer[name]();
    } else if (!info.dependencies) {
        instance = new components[name]();
    } else {
        const constructArguments: any[] = [];
        for (let i in info.dependencies) {
            const dependency = info.dependencies[i];
            const diType = dependency['di'] || false;
            const argCanBeNull = !!dependency.null;
            var argument: any = null; // d.null
            if (diType === <ScopeType>'PARENT') {
                argument = parent ? parent.inject(dependency.name) : (rootProvides[dependency.name] || null);
            } else if (params && (dependency.argName in params)) {
                argument = params[dependency.argName];
            }
            else if (dependency.default) {
                argument = dependency.default; // TODO: copy object or array
            } else if (dependency.builtIn) {
                argument = dependency.name === 'string' ? '' : 0;
            } else {
                argument = resolve(dependency.name, {}, argCanBeNull, parent);
            }
            if (diType === <ScopeType>'COMPONENT') {
                toProvide[dependency.name] = argument;
            }
            constructArguments.push(argument);
        }
        instance = new components[name](...constructArguments);
    }
    if (info.base) {
        const baseComponent = instance as BaseComponent<any>;
        baseComponent.__id = ++nextInstanceId + '';
        if (parent !== null) {
            baseComponent._provides = parent._provides;
            baseComponent._parent = parent;
        } else {
            baseComponent._provides = rootProvides;
        }
        for (let p in toProvide) {
            baseComponent.provide(p, toProvide[p]);
        }
    }

    // DI Props
    if (info.diProps) {
        for (let prop in info.diProps) {
            const dependency = info.diProps[prop];
            const diType = dependency.di;
            let propInstance = null;
            if (diType === 'PARENT') {
                propInstance = parent ? parent.inject(dependency.name) : (rootProvides[dependency.name] || null);
            } else if (diType === 'SINGLETON') {
                if (!(dependency.name in singletonContainer)) {
                    propInstance = resolve(dependency.name, {}, false, parent);
                    singletonContainer[dependency.name] = propInstance;
                }
                propInstance = singletonContainer[dependency.name];
            } else if (diType === 'SCOPED') {
                if (!(dependency.name in globalScope.scopedContainer)) {
                    propInstance = resolve(dependency.name, {}, false, parent);
                    globalScope.scopedContainer[dependency.name] = propInstance;
                }
                propInstance = globalScope.scopedContainer[dependency.name];
            } else {
                propInstance = resolve(dependency.name, {}, false, parent);
            }
            instance[prop] = propInstance;
            if (diType === 'COMPONENT') {
                (instance as BaseComponent<any>).provide(dependency.name, propInstance);
            }
        }
    }

    const scopeState = getScopeState();
    if (scopeState.state[name]) {
        for (let prop in scopeState.state[name]) {
            instance[prop] = scopeState.state[name][prop];
        }
    }
    if (container) {
        container[name] = instance;
    }
    return instance;
}