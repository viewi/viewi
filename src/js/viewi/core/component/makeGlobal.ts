import { BaseComponent } from "./baseComponent";
import { componentsMeta } from "./componentsMeta";
import { makeGlobalMethod } from "./makeGlobalMethod";

export function makeGlobal() {
    for (let key in componentsMeta.globals) {
        BaseComponent.prototype[key] = function () {
            BaseComponent.prototype[key] = makeGlobalMethod(key, componentsMeta.globals[key]);
            return BaseComponent.prototype[key].apply(null, arguments);
        }
    }
}