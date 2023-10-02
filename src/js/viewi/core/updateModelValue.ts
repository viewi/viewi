import { BaseComponent } from "./BaseComponent";
import { ModelHandler } from "./modelHandler";

export function updateModelValue(
    target: HTMLInputElement,
    instance: BaseComponent<any>,
    options: ModelHandler
): void {
    if (options.inputType === "checkbox") {
        const currentValue = options.getter(instance);
        if (Array.isArray(currentValue)) {
            const inputValue = target.value;
            const valuePosition = currentValue.indexOf(inputValue);
            if (valuePosition === -1) {
                target.removeAttribute('checked');
                target.checked = false;
            } else {
                target.setAttribute('checked', 'checked');
                target.checked = true;
            }
        } else {
            if (currentValue) {
                target.setAttribute('checked', 'checked');
                target.checked = true;
            } else {
                target.removeAttribute('checked');
                target.checked = false;
            }
        }
    } else if (options.inputType === "radio") {
        const currentValue = options.getter(instance);
        const inputValue = target.value;
        if (currentValue === inputValue) {
            target.setAttribute('checked', 'checked');
            target.checked = true;
        } else {
            target.removeAttribute('checked');
            target.checked = false;
        }
    } else {
        target.value = options.getter(instance);
    }
}