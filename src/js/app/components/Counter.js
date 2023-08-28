import { BaseComponent } from "./BaseComponent";

class Counter extends BaseComponent {
    _name = 'Counter';
    count = 0;

    increment() {
        this.$.count++;
    }

    decrement() {
        this.$.count--;
    }
}

export { Counter }