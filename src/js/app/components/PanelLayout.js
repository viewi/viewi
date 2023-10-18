import { BaseComponent } from "../../viewi/core/component/baseComponent";

class PanelLayout extends BaseComponent {
    _name = 'PanelLayout';
    title = "Viewi";
    timerId = 0;
    seconds = 0;

    init() {
        this.seconds = 500;
        /** JS injection **/
        this.timerId = setInterval(() => this.tick(), 1000);
        /** END injection **/;
    }

    destroy() {
        /** JS injection **/
        clearInterval(this.timerId);
        /** END injection **/;
    }

    tick() {
        this.seconds++;
        /** JS injection **/
        console.log('PanelLayout time ' + this.seconds);
        /** END injection **/;
    }
}

export const PanelLayout_x = [
    function (_component) { return _component.seconds; },
    function (_component) { return _component.title; },
    function (_component) { return "Panel: " + (_component.seconds ?? "") + " " + (_component.title ?? ""); }
];

export { PanelLayout }