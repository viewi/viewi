import { BaseComponent } from "./BaseComponent";
import { ModelHandler } from "./modelHandler";

export function getModelHandler(
    instance: BaseComponent<any>,
    options: ModelHandler
): EventListener {
    return <EventListener>function (event: Event & {
        target: HTMLInputElement
    }) {
        if (options.inputType === "checkbox") {
            const currentValue = options.getter(instance);
            const inputValue = event.target.value;
            if (Array.isArray(currentValue)) {
                const newValue = currentValue.slice();
                const valuePosition = newValue.indexOf(inputValue);
                if (valuePosition === -1) {
                    if (event.target.checked) {
                        newValue.push(inputValue);
                    }
                } else {
                    if (!event.target.checked) {
                        newValue.splice(valuePosition, 1);
                    }
                }
                options.setter(instance, newValue);
            } else {
                options.setter(instance, event.target.checked);
            }
        } else if (options.inputType === "radio") {
            const inputValue = event.target.value;
            options.setter(instance, inputValue);
        } else {
            options.setter(instance, event.target.value);
        }
    }
}