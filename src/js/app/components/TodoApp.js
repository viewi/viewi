import { BaseComponent } from "../../viewi/core/component/baseComponent";
import { strlen } from "../functions/strlen";
import { count } from "../functions/count";
import { TodoList } from "./TodoList";

class TodoApp extends BaseComponent {
    _name = 'TodoApp';
    text = "";
    items = [];

    handleSubmit(event) {
        var $this = this;
        event.preventDefault();
        if (strlen($this.text) == 0) {
            return;
        }
        $this.items = [...$this.items, $this.text];
        $this.text = "";
    }
}

export const TodoApp_x = [
    function (_component) { return function (event) { _component.handleSubmit(event); }; },
    function (_component) { return [function (_component) {
    return _component.text;
}, function (_component, value) {
    _component.text = value;
}]; },
    function (_component) { return "\n        Add #" + (count(_component.items) + 1 ?? "") + "\n    "; },
    function (_component) { return _component.items; }
];

export { TodoApp }