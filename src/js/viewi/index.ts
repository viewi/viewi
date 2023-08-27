import { BaseComponent } from "./core/BaseComponent";
import { makeProxy } from "./core/makeProxy";
import { render } from "./core/render";
import { HomeComponent, THomeComponent } from "./tests/HomeComponent";
import * as components from "./todo";

const Viewi = () => ({
    version: '2.0.0'
});
globalThis.Viewi = Viewi
export { Viewi };

// test

// const homeComponent = (new HomeComponent() as TGBaseComponent<THomeComponent>).$$p;
// console.log(homeComponent);
// globalThis.homeComponent = homeComponent;

// const target = document.getElementById('app');
// if (target !== null) {
//     render(target, homeComponent);
// }
// setInterval(() => homeComponent.count++, 1000);

export class Todo extends BaseComponent<Todo> {
    items: string[] = [];
    count: number = 0;
    $total: number = 0; // private var simulation
    reducer: any;
    _name: string = 'Todo';
    constructor(reducer: any) {
        super();
        this.reducer = reducer;
        this.$ = makeProxy(this);
    }

    increment() {
        this.$.count++;
        this.emitEvent('count', this.count);
    }
}
const b = new Todo({ count: 0 });
const c = new Todo({ count: 0 });

b.$$r['count'] = () => console.log('Count has changed', b.count);
b.$_callbacks['count'] = (event) => console.log('Event count', event);
b.increment();
b.increment();
b.increment();
b.increment();
console.log(b.increment(), c.increment());