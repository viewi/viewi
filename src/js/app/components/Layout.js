import { BaseComponent } from "../../viewi/core/BaseComponent";

class Layout extends BaseComponent {
    _name = 'Layout';
    title = 'Viewi';
}

export const Layout_x = [
    function (_component) { return _component.title; }
];

export { Layout }