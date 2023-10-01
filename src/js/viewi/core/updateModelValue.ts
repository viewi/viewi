import { BaseComponent } from "./BaseComponent";

export function updateModelValue(
    target: HTMLInputElement,
    instance: BaseComponent<any>,
    getter: (instance: BaseComponent<any>) => any,
    setter: (instance: BaseComponent<any>, value: any) => void
): void {
    target.value = getter(instance);
}