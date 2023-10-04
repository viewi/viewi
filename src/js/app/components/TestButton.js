import { BaseComponent } from "../../viewi/core/BaseComponent";

class TestButton extends BaseComponent {
    _name = 'TestButton';
    id = null;
    title = null;
    class = null;
    disabled = false;
    loading = false;
}

export const TestButton_x = [
    function (_component) { return _component.id; },
    function (_component) { return _component.disabled; },
    function (_component) { return _component.title; },
    function (_component) { return _component.class; },
    function (_component) { return " " + (_component.title ?? "") + "\n    "; },
    function (_component) { return _component.loading; }
];

export { TestButton }