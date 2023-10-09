import { BaseComponent } from "../component/baseComponent";
import { TemplateNode } from "../node/templateNode";
import { componentsMeta } from "../component/componentsMeta";
import { ContextScope } from "../lifecycle/contextScope";

export function renderAttributeValue(
    instance: BaseComponent<any>,
    attribute: TemplateNode,
    element: HTMLElement,
    attrName: string,
    scope: ContextScope
) {
    let valueContent: string | boolean | null = null;
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
        if (valueContent === true || valueContent === null) {
            attrName !== element.getAttribute(attrName) && element.setAttribute(attrName, attrName);
        } else {
            element.removeAttribute(attrName);
        }
    } else {
        if (valueContent !== null) {
            valueContent !== element.getAttribute(attrName) && element.setAttribute(attrName, <string>valueContent);
        } else {
            element.removeAttribute(attrName);
        }
    }
};