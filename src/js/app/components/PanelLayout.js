import { BaseComponent } from "../../viewi/core/component/baseComponent";

class PanelLayout extends BaseComponent {
    _name = 'PanelLayout';
    title = "Viewi";
}

export const PanelLayout_x = [
    function (_component) { return _component.title; },
    function (_component) { return "Panel: " + (_component.title ?? ""); }
];

export { PanelLayout }