import { CounterReducer } from "./CounterReducer";
import { BaseComponent } from "../../viewi/core/BaseComponent";

class StatefulCounter extends BaseComponent {
    _name = 'StatefulCounter';
    counter = null;
    $message = 'Secret message';
    count = null;

    constructor(count) {
        super();
        this.count = count === undefined ? 0 : count;
        this.counter = new CounterReducer();
    }

    $calculate() {
        this.$.count++;
    }
}

export const StatefulCounter_x = [
    function (_component) { return _component.__id; },
    function (_component) { return _component.counter.count; }
];

export { StatefulCounter }