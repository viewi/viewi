import { BaseComponent } from "../../viewi/core/BaseComponent";

class TestButton extends BaseComponent {
    _name = 'TestButton';
    id = null;
    title = null;
    class = null;
}

export const TestButton_x = [
    function (_component) { return _component.id; },
    function (_component) { return _component.title; },
    function (_component) { return _component.class; },
    function (_component) { return " " + (_component.title ?? "") + "\n"; }
];

export { TestButton }