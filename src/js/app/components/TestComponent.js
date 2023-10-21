import { UserModel } from "./UserModel";
import { CounterReducer } from "./CounterReducer";
import { BaseComponent } from "../../viewi/core/component/baseComponent";
import { json_encode } from "../functions/json_encode";
import { TodoApp } from "./TodoApp";
import { TestInput } from "./TestInput";
import { TestButton } from "./TestButton";
import { StatefulCounter } from "./StatefulCounter";
import { SomeComponent } from "./SomeComponent";
import { Counter } from "./Counter";

class TestComponent extends BaseComponent {
    _name = 'TestComponent';
    name = "MyName";
    name2 = "";
    _name2_Test = "MyName_2";
    empty = "";
    null = null;
    url = "\/home";
    attr = "title";
    event = "(click)";
    arr = ["a", "b", "c"];
    arrWithKeys = {"a": "Apple", "b": "Orange", "c": "Lemon"};
    arrNested = {"a": {"a": "Apple", "b": "Orange", "c": "Lemon"}, "b": {"a": "Apple", "b": "Orange", "c": "Lemon"}, "c": {"a": "Apple", "b": "Orange", "c": "Lemon"}};
    ifValue = true;
    ifElseValue = true;
    nestedIf = true;
    dynamic = "div";
    dynamic2 = "ItemComponent";
    raw = "<b><i>Raw html text<\/i><\/b>";
    isDisabled = true;
    message = "Some message";
    checked = false;
    checked2 = true;
    checkedNames = [];
    picked = "One";
    selected = "";
    selectedList = ["A", "C"];
    user = null;
    NameInput = null;
    testModel = "some test";
    counterReducer = null;

    constructor(counterReducer) {
        super();
        var $this = this;
        $this.counterReducer = counterReducer;
        $this.user = new UserModel();
        $this.user.id = 1;
        $this.user.name = "Miki the cat";
        $this.counterReducer.increment();
    }

    getNames() {
        var $this = this;
        return json_encode($this.checkedNames);
    }

    getName(name) {
        var $this = this;
        name = typeof name !== 'undefined' ? name : null;
        var sum = (1 + 5) * 10;
        return name ?? "DefaultName";
    }

    addTodo() {
        var $this = this;
        $this.arrNested = {"a": {"a": "Apple", "b": "Orange", "c": "Lemon"}, "d": {"R": "Rat", "T": "Dog", "G": "Cat"}, "b": {"a": "Apple", "b": "Orange", "c": "Lemon"}};
        // Test cases
        // $this->arr = ['E', 'a'];
        // $this->arr = ['c', 'b', 'a'];
        // $this->arr = ['c', 'b', 'c', 'c'];
        // $this->arr = [...$this->arr, 'Viewi', ...$this->arr];
        // $this->arr = ['g', 'b', 'a', 'c'];
    }

    onEvent(event) {
        var $this = this;
        event.preventDefault();
    }

    toggleIf() {
        var $this = this;
        $this.ifValue = !$this.ifValue;
        $this.arr = $this.ifValue ? ["a", "b", "c"] : ["x", "b", "r"];
    }

    toggleElseIf() {
        var $this = this;
        $this.ifElseValue = !$this.ifElseValue;
    }
}

export const TestComponent_x = [
    function (_component) { return "Tag test " + (_component.name ?? "") + " " + (_component.name2 ?? "") + " " + (_component._name2_Test ?? ""); },
    function (_component) { return "\n    $notAVar " + (_component.getName() ?? "") + " " + (_component.getName(_component.name) ?? "") + "\n    Nested\n    "; },
    function (_component) { return _component.url; },
    function (_component) { return _component.empty; },
    function (_component) { return _component.null; },
    function (_component) { return _component.attr; },
    function (_component) { return function (event) { expression(event); }; },
    function (_component) { return _component.event; },
    function (_component) { return function (event) { _component.onEvent(event); }; },
    function (_component) { return [function (_component) {
    return _component.testModel;
}, function (_component, value) {
    _component.testModel = value;
}]; },
    function (_component) { return _component.testModel; },
    function (_component) { return [function (_component) {
    return _component.testModel;
}, function (_component, value) {
    _component.testModel = value;
}]; },
    function (_component) { return function () { _component.counterReducer.increment(); }; },
    function (_component) { return "Clicked " + (_component.counterReducer.count ?? "") + "\n"; },
    function (_component) { return function () { _component.counterReducer.increment(); }; },
    function (_component) { return "Clicked " + (_component.counterReducer.count ?? ""); },
    function (_component) { return function (event) { _component.counterReducer.increment(event); }; },
    function (_component) { return "Clicked " + (_component.counterReducer.count ?? ""); },
    function (_component) { return _component.__id; },
    function (_component) { return "First Name (" + (_component.__id ?? "") + ")"; },
    function (_component) { return _component.__id; },
    function (_component) { return function (event) { _component.counterReducer.increment(); }; },
    function (_component) { return "Clicked " + (_component.counterReducer.count ?? ""); },
    function (_component) { return function (event) { _component.nestedIf = !_component.nestedIf; }; },
    function (_component) { return _component.nestedIf; },
    function (_component) { return [function (_component) {
    return _component.user.name;
}, function (_component, value) {
    _component.user.name = value;
}]; },
    function (_component) { return _component.user.name; },
    function (_component) { return _component.name; },
    function (_component) { return "Custom " + (_component.name ?? ""); },
    function (_component) { return [function (_component) {
    return _component.name;
}, function (_component, value) {
    _component.name = value;
}]; },
    function (_component) { return [function (_component) {
    return _component.name;
}, function (_component, value) {
    _component.name = value;
}]; },
    function (_component) { return "\n    " + (_component.name ?? "") + "\n"; },
    function (_component) { return [function (_component) {
    return _component.name2;
}, function (_component, value) {
    _component.name2 = value;
}]; },
    function (_component) { return "\n    " + (_component.name2 ?? "") + "\n"; },
    function (_component) { return [function (_component) {
    return _component.message;
}, function (_component, value) {
    _component.message = value;
}]; },
    function (_component) { return _component.message; },
    function (_component) { return [function (_component) {
    return _component.checked;
}, function (_component, value) {
    _component.checked = value;
}]; },
    function (_component) { return _component.checked; },
    function (_component) { return [function (_component) {
    return _component.checked2;
}, function (_component, value) {
    _component.checked2 = value;
}]; },
    function (_component) { return _component.checked2; },
    function (_component) { return [function (_component) {
    return _component.checkedNames;
}, function (_component, value) {
    _component.checkedNames = value;
}]; },
    function (_component) { return [function (_component) {
    return _component.checkedNames;
}, function (_component, value) {
    _component.checkedNames = value;
}]; },
    function (_component) { return [function (_component) {
    return _component.checkedNames;
}, function (_component, value) {
    _component.checkedNames = value;
}]; },
    function (_component) { return "Checked names: " + (_component.getNames() ?? ""); },
    function (_component) { return [function (_component) {
    return _component.picked;
}, function (_component, value) {
    _component.picked = value;
}]; },
    function (_component) { return [function (_component) {
    return _component.picked;
}, function (_component, value) {
    _component.picked = value;
}]; },
    function (_component) { return "Picked: " + (_component.picked ?? ""); },
    function (_component) { return [function (_component) {
    return _component.selected;
}, function (_component, value) {
    _component.selected = value;
}]; },
    function (_component) { return "Selected: " + (_component.selected ?? ""); },
    function (_component) { return [function (_component) {
    return _component.selectedList;
}, function (_component, value) {
    _component.selectedList = value;
}]; },
    function (_component) { return [function (_component) {
    return _component.selectedList;
}, function (_component, value) {
    _component.selectedList = value;
}]; },
    function (_component) { return "Selected: " + (json_encode(_component.selectedList) ?? ""); },
    function (_component) { return _component.isDisabled; },
    function (_component) { return !_component.isDisabled; },
    function (_component) { return _component.isDisabled ? " mui-btn" : ""; },
    function (_component) { return _component.isDisabled ? " mui-btn--primary" : ""; },
    function (_component) { return !_component.isDisabled ? " mui-btn--accent" : ""; },
    function (_component) { return function (event) { _component.isDisabled = !_component.isDisabled; }; },
    function (_component) { return function (event) { _component.isDisabled = !_component.isDisabled; }; },
    function (_component) { return _component.raw; },
    function (_component) { return _component.raw; },
    function (_component) { return function (event) { _component.raw = _component.raw[0] === "<" ? "New RAW: <span><i>Another content<\/i><\/span>" : "<b><i>Raw html text<\/i><\/b>"; }; },
    function (_component) { return _component.nestedIf; },
    function (_component) { return _component.name; },
    function (_component) { return "Custom " + (_component.name ?? ""); },
    function (_component) { return _component.nestedIf; },
    function (_component) { return {"id": "myid", "title": "Custom " + _component.name, "class": "mui-btn--accent"}; },
    function (_component) { return "\n    Custom " + (_component.name ?? "") + "\n"; },
    function (_component) { return function (event) { _component.name = "Viewi Junior"; }; },
    function (_component) { return function (event) { _component.nestedIf = !_component.nestedIf; }; },
    function (_component) { return _component.nestedIf; },
    function (_component) { return "Custom " + (_component.name ?? "") + " Slot"; },
    function (_component) { return _component.nestedIf; },
    function (_component) { return _component.arrNested; },
    function (_component, key, subArr) { return "\n    Custom " + (_component.name ?? "") + " Slot\n    "; },
    function (_component, key, subArr) { return subArr; },
    function (_component, key, subArr, subKey, subItem) { return key; },
    function (_component, key, subArr, subKey, subItem) { return subKey; },
    function (_component, key, subArr, subKey, subItem) { return subItem; },
    function (_component, key, subArr, subKey, subItem) { return key + ". " + (subKey ?? "") + ". " + (subItem ?? ""); },
    function (_component, key, subArr) { return _component.nestedIf; },
    function (_component, key, subArr) { return _component.name; },
    function (_component) { return function (event) { _component.nestedIf = !_component.nestedIf; }; },
    function (_component) { return function (event) { _component.dynamic = _component.dynamic === "div" ? "ItemComponent" : "div"; }; },
    function (_component) { return "\n" + (_component.dynamic ?? "") + " " + (_component.dynamic2 ?? "") + "\n"; },
    function (_component) { return _component.dynamic; },
    function (_component) { return "Tag or component " + (_component.dynamic ?? "") + " " + (_component.dynamic2 ?? ""); },
    function (_component) { return _component.dynamic2; },
    function (_component) { return "Tag or component " + (_component.dynamic ?? "") + " " + (_component.dynamic2 ?? ""); },
    function (_component) { return "Custom " + (_component.name ?? "") + " Slot"; },
    function (_component) { return "Custom " + (_component.name ?? "") + " slot\n        "; },
    function (_component) { return "Custom header " + (_component.name ?? "") + " inside div"; },
    function (_component) { return "Custom " + (_component.name ?? "") + " footer"; },
    function (_component) { return function (event) { _component.addTodo(event); }; },
    function (_component) { return _component.nestedIf; },
    function (_component) { return _component.name; },
    function (_component) { return _component.ifValue; },
    function (_component) { return _component.arrNested; },
    function (_component, key, subArr) { return subArr; },
    function (_component, key, subArr, subKey, subItem) { return key + ". " + (subKey ?? "") + ". " + (subItem ?? ""); },
    function (_component) { return _component.arr; },
    function (_component, _key1, item) { return _component.ifElseValue; },
    function (_component, _key1, item) { return item; },
    function (_component, _key1, item) { return item; },
    function (_component, _key1, item) { return item; },
    function (_component, _key1, item) { return _component.nestedIf; },
    function (_component, _key1, item) { return _component.name; },
    function (_component) { return _component.arr; },
    function (_component, index, item) { return index; },
    function (_component, index, item) { return item; },
    function (_component, index, item) { return index + ". "; },
    function (_component, index, item) { return item; },
    function (_component, index, item) { return item; },
    function (_component) { return _component.arrWithKeys; },
    function (_component, index, item) { return index; },
    function (_component, index, item) { return item; },
    function (_component, index, item) { return index + ": "; },
    function (_component, index, item) { return index; },
    function (_component, index, item) { return item; },
    function (_component, index, item) { return item; },
    function (_component) { return _component.ifValue; },
    function (_component) { return _component.arrNested; },
    function (_component, key, subArr) { return subArr; },
    function (_component, key, subArr, subKey, subItem) { return key; },
    function (_component, key, subArr, subKey, subItem) { return subKey; },
    function (_component, key, subArr, subKey, subItem) { return subItem; },
    function (_component, key, subArr, subKey, subItem) { return key + ". " + (subKey ?? "") + ". " + (subItem ?? ""); },
    function (_component) { return _component.arrNested; },
    function (_component, key, subArr) { return key === "b"; },
    function (_component, key, subArr) { return subArr; },
    function (_component, key, subArr, subKey, subItem) { return key; },
    function (_component, key, subArr, subKey, subItem) { return subKey; },
    function (_component, key, subArr, subKey, subItem) { return subItem; },
    function (_component, key, subArr, subKey, subItem) { return key + ". " + (subKey ?? "") + ". " + (subItem ?? ""); },
    function (_component) { return function (event) { _component.toggleIf(event); }; },
    function (_component) { return function (event) { _component.toggleElseIf(event); }; },
    function (_component) { return function (event) { _component.nestedIf = !_component.nestedIf; }; },
    function (_component) { return function (event) { _component.name = "Viewi Junior"; }; },
    function (_component) { return _component.ifValue; },
    function (_component) { return _component.ifElseValue; },
    function (_component) { return _component.ifValue; },
    function (_component) { return _component.ifElseValue; },
    function (_component) { return _component.arr; },
    function (_component, _key2, item) { return item; }
];

export { TestComponent }