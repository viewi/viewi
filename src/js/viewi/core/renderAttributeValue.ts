import { BaseComponent } from "./BaseComponent";
import { TemplateNode } from "./node";
import { ContextScope } from "./contextScope";

export function renderAttributeValue(
    instance: BaseComponent<any>,
    attribute: TemplateNode,
    element: HTMLElement,
    attrName: string,
    scope?: ContextScope
) {
    let valueContent: string | null = null;
    if (attribute.children) {
        valueContent = '';
        for (let av = 0; av < attribute.children.length; av++) {
            const attributeValue = attribute.children[av];
            let callArguments = [instance];
            if (scope) {
                callArguments = callArguments.concat(scope.arguments);
            }
            const childContent = attributeValue.expression
                ? instance.$$t[attributeValue.code as number].apply(null, callArguments)
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