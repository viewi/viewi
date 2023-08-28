import { BaseComponent } from "./BaseComponent";
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

export { TodoApp }