import { BaseComponent } from "../../viewi/core/BaseComponent";
import { strlen } from "../functions/strlen";
import { count } from "../functions/count";

class TodoApp extends BaseComponent {
    _name = 'TodoApp';
    text = '';
    items = [];

    handleSubmit(event) {
        event.preventDefault();
        if (strlen(this.$.text) == 0) {
            return;
        }
        this.$.items.push(this.$.text);
        this.$.text = '';
    }
}

export const TodoApp_x = [
    function (_component) { return _component.text; },
    function (_component) { return count(_component.items) + 1; },
    function (_component) { return _component.items; }
];

export { TodoApp }