import { BaseComponent } from "../../viewi/core/component/baseComponent";

class TestButton extends BaseComponent {
    _name = 'TestButton';
    id = null;
    title = null;
    class = null;
    disabled = false;
    loading = false;

    onClick(event) {
        var $this = this;
        $this.emitEvent("click", event);
    }
}

export const TestButton_x = [
    function (_component) { return _component.id; },
    function (_component) { return _component.disabled; },
    function (_component) { return _component.title; },
    function (_component) { return _component.class; },
    function (_component) { return function (event) { _component.onClick(event); }; },
    function (_component) { return " " + (_component.title ?? "") + "\n    "; },
    function (_component) { return _component.loading; }
];

export { TestButton }