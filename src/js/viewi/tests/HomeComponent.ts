import { BaseComponent, TBaseComponent, TGBaseComponent } from "../core/BaseComponent";
import { makeProxy } from "../core/makeProxy";

export type THomeComponent = {
    value: string | null;
    count: number;
    dynamicTagOrComponent1: string;
    dynamicTagOrComponent2: string;
    _element?: HTMLElement | null,
    getName: () => string;
    onClick: (event: any) => void;
    increment: () => void;
};

export function HomeComponent(this: THomeComponent & TGBaseComponent<THomeComponent>) {
    BaseComponent.apply(this);
    const $this = makeProxy(this);
    // props
    this.value = null;
    this.count = 0;
    this.dynamicTagOrComponent1 = 'ListItem';
    this.dynamicTagOrComponent2 = 'span';
    // methods
    this.getName = function () {
        return 'My name ' + ($this.value || 'Anon');
    };
    this.onClick = function (event) {
        console.log('Clicked');
        $this.value = $this.value ? null : 'My Text';
    };
    this.increment = function () {
        $this.count++;
    }
};