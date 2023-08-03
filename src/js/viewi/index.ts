import { TGBaseComponent } from "./core/baseComponent";
import { render } from "./core/render";
import { HomeComponent, THomeComponent } from "./tests/HomeComponent";

const Viewi = () => ({
    version: '2.0.0'
});
globalThis.Viewi = Viewi
export { Viewi };

// test

const homeComponent = (new HomeComponent() as TGBaseComponent<THomeComponent>).$$p;
console.log(homeComponent);
globalThis.homeComponent = homeComponent;

const target = document.getElementById('app');
if (target !== null) {
    render(target, homeComponent);
}
setInterval(() => homeComponent.count++, 1000);