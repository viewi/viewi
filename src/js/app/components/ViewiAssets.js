import { BaseComponent } from "../../viewi/core/component/baseComponent";

class ViewiAssets extends BaseComponent {
    _name = 'ViewiAssets';
    appPath = "";
    data = "<script data-keep=\"ViewiAssets\">\"ViewiAssets\";<\/script>";
}

export const ViewiAssets_x = [
    function (_component) { return _component.data; },
    function (_component) { return _component.appPath; }
];

export { ViewiAssets }