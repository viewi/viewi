import { BaseComponent } from "../../viewi/core/component/baseComponent";
import { strlen } from "../functions/strlen";

class Counter extends BaseComponent {
    _name = 'Counter';
    count = 0;
    message = "My message";

    increment() {
        var $this = this;
        $this.count++;
        $this.message += "!";
    }

    decrement() {
        var $this = this;
        $this.count--;
    }
}

export const Counter_x = [
    function (_component) { return function (event) { _component.decrement(); }; },
    function (_component) { return _component.count % 10 + 12; },
    function (_component) { return "\n    Count " + (_component.count ?? "") + " " + (strlen(_component.message) ?? "") + "\n"; },
    function (_component) { return "\nCount " + (_component.count ?? "") + " strlen:" + (strlen(_component.message) ?? "") + "\n"; },
    function (_component) { return function (event) { _component.count++; }; },
    function (_component) { return function (event) { _component.increment(); }; },
    function (_component) { return function (event) { _component.increment(event); }; },
    function (_component) { return function (event) { _component.increment(event); }; },
    function (_component) { return _component.message; }
];

export { Counter }