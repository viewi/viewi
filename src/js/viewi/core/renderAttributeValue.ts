import { BaseComponent } from "./BaseComponent";
import { TemplateNode } from "./node";

export function renderAttributeValue(
    instance: BaseComponent<any>,
    attribute: TemplateNode,
    element: HTMLElement,
    attrName: string
) {
    let valueContent: string | null = null;
    if (attribute.children) {
        valueContent = '';
        for (let av = 0; av < attribute.children.length; av++) {
            const attributeValue = attribute.children[av];
            const childContent = attributeValue.expression
                ? instance.$$t[attributeValue.code as number](instance)
                : (attributeValue.content ?? '');
            valueContent = av === 0 ? childContent : valueContent + (childContent ?? '');
        }
    }
    if (valueContent !== null) {
        valueContent !== element.getAttribute(attrName) && element.setAttribute(attrName, valueContent);
    } else {
        element.removeAttribute(attrName);
    }
};