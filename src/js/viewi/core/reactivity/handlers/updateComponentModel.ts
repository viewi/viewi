import { BaseComponent } from "../../component/baseComponent";

export function updateComponentModel(
    instance: BaseComponent<any>,
    attrName: string,
    getter: (instance: BaseComponent<any>) => any,
    parentInstance: BaseComponent<any>
) {
    instance[attrName] = getter(parentInstance);
}