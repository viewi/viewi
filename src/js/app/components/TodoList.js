import { BaseComponent } from "../../viewi/core/component/baseComponent";

class TodoList extends BaseComponent {
    _name = 'TodoList';
    items = null;
}

export const TodoList_x = [
    function (_component) { return _component.items; },
    function (_component, _key1, item) { return item; }
];

export { TodoList }