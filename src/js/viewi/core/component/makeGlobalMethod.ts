import { resolve } from "../di/resolve";

export function makeGlobalMethod(method: string, typeName: string) {
    const instance = resolve(typeName);
    return function () {
        return instance[method].apply(instance, arguments);
    }
}