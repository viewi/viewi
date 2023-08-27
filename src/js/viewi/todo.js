import { baseComponent } from "./core/BaseComponent";
import { makeProxy } from "./core/makeProxy";

export const todo = {
    create(child) {
        let a = 1;
        const base = child || {};
        // TODO: consider PHP extend and parent methods access
        baseComponent.create(base);
        base._name_ = 'Todo';
        const $this = makeProxy(base);
        base.count = 0;
        base.text = '';
        base.items = [];

        base.__construct = function (counter) {
            this.count = counter;
        };

        base.handleSubmit = function (event) {
            event.preventDefault();
            if ($this.text.length == 0) {
                return;
            }
            $this.items.push($this.text);
            $this.text = '';
        };

        base.increment = function () {
            $this.count++;
            return ++a;
        }
        return $this;
    }
}