import { BaseComponent } from "../../viewi/core/BaseComponent";
import { strlen } from "../functions/strlen";

class Counter extends BaseComponent {
    _name = 'Counter';
    count = 0;
    message = 'My message';

    increment() {
        this.$.count++;
    }

    decrement() {
        this.$.count--;
    }
}

export const Counter_x = [
    function (_component) { return _component.count; },
    function (_component) { return strlen(_component.message); }
];

export { Counter }