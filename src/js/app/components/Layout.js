import { BaseComponent } from "../../viewi/core/BaseComponent";

class Layout extends BaseComponent {
    _name = 'Layout';
    title = "Viewi";
}

export const Layout_x = [
    function (_component) { return "\n        " + (_component.title ?? "") + " | Viewi\n    "; }
];

export { Layout }