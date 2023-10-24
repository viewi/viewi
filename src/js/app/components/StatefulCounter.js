import { CounterReducer } from "./CounterReducer";
import { BaseComponent } from "../../viewi/core/component/baseComponent";

class StatefulCounter extends BaseComponent {
    _name = 'StatefulCounter';
    counter = null;
    count = null;
    constructor(counter, count) {
        super();
        var $this = this;
        $this.counter = counter;
        $this.count = count === undefined ? 0 : count;
        $this.counter.count+=100;
    }
}

export const StatefulCounter_x = [
    function (_component) { return function (event) { _component.counter.decrement(); }; },
    function (_component) { return _component.__id; },
    function (_component) { return _component.counter.count; },
    function (_component) { return function (event) { _component.counter.increment(); }; }
];

export { StatefulCounter }