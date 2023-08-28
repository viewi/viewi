// import { Counter, TodoReducer, CounterReducer } from "../app/components";
import { components } from "../app/components";
import * as functions from "../app/functions";

// const components: { [key: string]: any} = {
//     Counter,
//     CounterReducer,
//     TodoReducer
// };

const Viewi = () => ({
    version: '2.0.1'
});
globalThis.Viewi = Viewi
export { Viewi };

for (let i in components) {
    const component = components[i];
    const instance = new component();
    console.log(component, instance);
}