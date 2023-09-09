import { BaseComponent } from "../../viewi/core/BaseComponent";

class HomePage extends BaseComponent {
    _name = 'HomePage';
    title = "Viewi v2 - Build reactive front-end with PHP";
}

export const HomePage_x = [
    function (_component) { return _component.title; },
    function (_component) { return _component.title; }
];

export { HomePage }