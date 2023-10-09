import { BaseComponent } from "../../component/baseComponent";

export function getComponentModelHandler(instance: BaseComponent<any>, setter: (instance: BaseComponent<any>, value: any) => void) {
    return function (event: any) {
        setter(instance, event);
    }
}