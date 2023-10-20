import { BaseComponent } from "../../viewi/core/component/baseComponent";

class TestInput extends BaseComponent {
    _name = 'TestInput';
    id = null;
    model = null;

    onInput(event) {
        var $this = this;
        $this.emitEvent("model", event.target.value);
    }
}

export const TestInput_x = [
    function (_component) { return function (event) { _component.onInput(event); }; },
    function (_component) { return [function (_component) {
    return _component.model;
}, function (_component, value) {
    _component.model = value;
}]; }
];

export { TestInput }