import { BaseComponent } from "../../component/baseComponent";
import { ModelHandler } from "./modelHandler";

export type HTMLModelInputElement = HTMLInputElement & HTMLSelectElement;

export function updateModelValue(
    target: HTMLModelInputElement,
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
    } else if (options.isMultiple || target.multiple) {
        const inputOptions = target.options;
        const currentValue = options.getter(instance);
        for (let i = 0; i < inputOptions.length; i++) {
            const currentOption = inputOptions[i];
            const index = currentValue.indexOf(currentOption.value);
            if (index === -1) {
                currentOption.selected = false;
            } else {
                currentOption.selected = true;
            }
        }
    } else {
        target.value = options.getter(instance);
    }
}