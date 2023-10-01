import { BaseComponent } from "./BaseComponent";

export function getModelHandler(
    instance: BaseComponent<any>,
    getter: (instance: BaseComponent<any>) => any,
    setter: (instance: BaseComponent<any>, value: any) => void
): EventListener {
    return <EventListener>function (event: Event & {
        target: HTMLInputElement
    }) {
        setter(instance, event.target.value);
    }
}