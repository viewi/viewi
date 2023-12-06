import { components } from "../../../app/main/components";
import { BaseComponent } from "../component/baseComponent";
import { componentsMeta } from "../component/componentsMeta";
import { getScopeState } from "../lifecycle/scopeState";
import { DIContainer } from "./diContainer";
import { factoryContainer } from "./factory";
import { globalScope } from "./globalScope";

const singletonContainer: DIContainer = {};
let nextInstanceId = 0;

export function resolve(name: string, params: { [key: string]: any } = {}, canBeNull: boolean = false) {
    if (!(name in componentsMeta.list)) {
        if (canBeNull) {
            return null;
        }
        throw new Error("Can't resolve " + name);
    }
    const info = componentsMeta.list[name];
    let instance: any = null;
    let container: boolean | DIContainer = false;
    if (info.di === "Singleton") {
        container = singletonContainer;
    } else if (info.di === "Scoped") {
        container = globalScope.scopedContainer;
    }
    if (container && (name in container)) {
        // console.log('Returning from cache', name, container[name]);
        return container[name];
    }
    if (info.custom) {
        instance = factoryContainer[name]();
    } else if (!info.dependencies) {
        instance = new components[name]();
    } else {
        const constructArguments: any[] = [];
        for (let i in info.dependencies) {
            const dependency = info.dependencies[i];
            const argCanBeNull = !!dependency.null;
            var argument: any = null; // d.null
            if (params && (dependency.argName in params)) {
                argument = params[dependency.argName];
            }
            else if (dependency.default) {
                argument = dependency.default; // TODO: copy object or array
            } else if (dependency.builtIn) {
                argument = dependency.name === 'string' ? '' : 0;
            } else {
                argument = resolve(dependency.name, {}, argCanBeNull);
            }
            constructArguments.push(argument);
        }
        instance = new components[name](...constructArguments);
    }
    if (info.base) {
        (<BaseComponent<any>>instance).__id = ++nextInstanceId + '';
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