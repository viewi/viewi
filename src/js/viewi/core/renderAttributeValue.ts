import { BaseComponent } from "./BaseComponent";
import { TemplateNode } from "./templateNode";
import { ContextScope } from "./contextScope";
import componentsMeta from "./componentsMeta";

export function renderAttributeValue(
    instance: BaseComponent<any>,
    attribute: TemplateNode,
    element: HTMLElement,
    attrName: string,
    scope: ContextScope
) {
    let valueContent: string | null = null;
    if (attribute.children) {
        valueContent = '';
        for (let av = 0; av < attribute.children.length; av++) {
            const attributeValue = attribute.children[av];
            let callArguments = [instance];
            if (scope.arguments) {
                callArguments = callArguments.concat(scope.arguments);
            }
            const childContent = attributeValue.expression
                ? instance.$$t[attributeValue.code as number].apply(null, callArguments)
                : (attributeValue.content ?? '');
            valueContent = av === 0 ? childContent : valueContent + (childContent ?? '');
        }
    }
    if (attrName.toLowerCase() in componentsMeta.booleanAttributes) {
        if (valueContent) {
            attrName !== element.getAttribute(attrName) && element.setAttribute(attrName, attrName);
        } else {
            element.removeAttribute(attrName);
        }
    } else {
        if (valueContent !== null) {
            valueContent !== element.getAttribute(attrName) && element.setAttribute(attrName, valueContent);
        } else {
            element.removeAttribute(attrName);
        }
    }
};