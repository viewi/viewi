import { TBaseComponent } from "./BaseComponent";

const document = globalThis.document as any as { 
    _c: (tagName: string, options?: ElementCreationOptions) => HTMLElement,
    createElement: (tagName: string, options?: ElementCreationOptions) => HTMLElement
};
document._c = document.createElement;
export function render(target: HTMLElement, component: any) {
    console.log('Rendering', target, component);
    var div = document._c('div');
    var span = document._c('span');
    span.textContent = component.value || '';
    div.appendChild(span);

    var button = document._c('button');
    button.textContent = 'Switch';

    var button2 = document._c('button');
    button2.textContent = 'Clicked ' + component.count + ' times';
    //document.createTextNode(val);

    target.appendChild(div);
    target.appendChild(button);
    target.appendChild(button2);

    component.$$r = {
        'count': () => button2.textContent = 'Clicked ' + component.count + ' times',
        'value': () => span.textContent = component.value || ''
    };
    var dispose = [
        button.addEventListener('click', component.onClick),
        button2.addEventListener('click', component.increment),
    ];
    console.log(component);
}

export { document };