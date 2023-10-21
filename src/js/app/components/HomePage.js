import { BaseComponent } from "../../viewi/core/component/baseComponent";
import { PanelLayout } from "./PanelLayout";

class HomePage extends BaseComponent {
    _name = 'HomePage';
    title = "Viewi v2 - Build reactive front-end with PHP";
    timerId = 0;
    seconds = 0;

    init() {
        var $this = this;
        $this.seconds = 100;
        /** JS injection **/
        this.timerId = setInterval(() => $this.tick(), 1000);
        /** END injection **/;
    }

    destroy() {
        var $this = this;
        /** JS injection **/
        clearInterval(this.timerId);
        /** END injection **/;
    }

    tick() {
        var $this = this;
        $this.seconds++;
        /** JS injection **/
        console.log('HomePage time ' + $this.seconds);
        /** END injection **/;
    }
}

export const HomePage_x = [
    function (_component) { return _component.title; },
    function (_component) { return _component.title; },
    function (_component) { return "Seconds: " + (_component.seconds ?? ""); }
];

export { HomePage }