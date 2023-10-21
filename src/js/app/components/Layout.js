import { BaseComponent } from "../../viewi/core/component/baseComponent";
import { MenuBar } from "./MenuBar";
import { DemoContainer } from "./DemoContainer";
import { ViewiAssets } from "./ViewiAssets";

class Layout extends BaseComponent {
    _name = 'Layout';
    title = "Viewi";
}

export const Layout_x = [
    function (_component) { return "\n        " + (_component.title ?? "") + " | Viewi\n    "; }
];

export { Layout }