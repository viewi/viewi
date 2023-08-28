import { TodoReducer } from "./TodoReducer";
import { BaseComponent } from "./BaseComponent";
import { strlen } from "../functions/strlen";
import { count } from "../functions/count";

class StatefulTodoApp extends BaseComponent {
    _name = 'StatefulTodoApp';
    text = '';
    todo = null;

    constructor(todo) {
        super();
        this.todo = todo;
        this.$ = makeProxy(this);
    }

    handleSubmit(event) {
        event.preventDefault();
        if (strlen(this.$.text) == 0) {
            return;
        }
        this.$.todo.addNewItem(this.$.text);
        this.$.text = '';
    }
}

export { StatefulTodoApp }