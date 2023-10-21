import { TodoReducer } from "./TodoReducer";
import { BaseComponent } from "../../viewi/core/component/baseComponent";
import { strlen } from "../functions/strlen";
import { count } from "../functions/count";
import { TodoList } from "./TodoList";

class StatefulTodoApp extends BaseComponent {
    _name = 'StatefulTodoApp';
    text = "";
    todo = null;

    constructor(todo) {
        super();
        var $this = this;
        $this.todo = todo;
    }

    handleSubmit(event) {
        var $this = this;
        event.preventDefault();
        if (strlen($this.text) == 0) {
            return;
        }
        $this.todo.addNewItem($this.text);
        $this.text = "";
    }
}

export const StatefulTodoApp_x = [
    function (_component) { return function (event) { _component.handleSubmit(event); }; },
    function (_component) { return [function (_component) {
    return _component.text;
}, function (_component, value) {
    _component.text = value;
}]; },
    function (_component) { return "\n        Add #" + (count(_component.todo.items) + 1 ?? "") + "\n    "; },
    function (_component) { return _component.todo.items; }
];

export { StatefulTodoApp }