import { TodoReducer } from "./TodoReducer";
import { BaseComponent } from "../../viewi/core/BaseComponent";
import { strlen } from "../functions/strlen";
import { count } from "../functions/count";

class StatefulTodoApp extends BaseComponent {
    _name = 'StatefulTodoApp';
    text = "";
    todo = null;

    constructor(todo) {
        super();
        this.todo = todo;
    }

    handleSubmit(event) {
        event.preventDefault();
        if (strlen(this.text) == 0) {
            return;
        }
        this.todo.addNewItem(this.text);
        this.text = "";
    }
}

export const StatefulTodoApp_x = [
    function (_component) { return function (event) { _component.handleSubmit(event); }; },
    function (_component) { return [function(_component) {
    return _component.text;
}, function(_component, value) {
    _component.text = value;
}]; },
    function (_component) { return "\n        Add #" + (count(_component.todo.items) + 1 ?? "") + "\n    "; },
    function (_component) { return _component.todo.items; }
];

export { StatefulTodoApp }