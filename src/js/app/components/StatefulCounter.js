import { CounterReducer } from "./CounterReducer";
import { BaseComponent } from "./BaseComponent";

class StatefulCounter extends BaseComponent {
    _name = 'StatefulCounter';
    counter = null;
    $message = 'Secret message';
    count = null;

    constructor(count) {
        super();
        this.count = count === undefined ? 0 : count;
        this.counter = new CounterReducer();
        this.$ = makeProxy(this);
    }

    $calculate() {
        this.$.count++;
    }
}

export { StatefulCounter }