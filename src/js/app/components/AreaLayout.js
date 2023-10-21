import { BaseComponent } from "../../viewi/core/component/baseComponent";
import { MenuBar } from "./MenuBar";
import { DemoContainer } from "./DemoContainer";

class AreaLayout extends BaseComponent {
    _name = 'AreaLayout';
    title = "Area Layout";
}

export const AreaLayout_x = [
    function (_component) { return "\n        " + (_component.title ?? "") + " | Area\n    "; }
];

export { AreaLayout }