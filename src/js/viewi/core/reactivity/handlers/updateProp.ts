import { BaseComponent } from "../../component/baseComponent";
import { TemplateNode } from "../../node/templateNode";
import { PropsContext } from "../../lifecycle/propsContext";

export function updateProp(instance: BaseComponent<any>, attribute: TemplateNode, props: PropsContext) {
    const parentInstance = props.scope.instance;
    const attrName = attribute.expression
        ? parentInstance.$$t[attribute.code!](parentInstance) // TODO: arguments
        : (attribute.content ?? '');
    if (attrName[0] === '(') {
        // TODO: event
    } else {
        let valueContent: any = null;
        let valueSubs = []; // TODO: on backend, pass attribute value subs in attribute
        if (attribute.children) {
            for (let av = 0; av < attribute.children.length; av++) {
                const attributeValue = attribute.children[av];
                let callArguments = [parentInstance];
                if (props.scope.arguments) {
                    callArguments = callArguments.concat(props.scope.arguments);
                }
                const childContent = attributeValue.expression
                    ? parentInstance.$$t[attributeValue.code as number].apply(null, callArguments)
                    : (attributeValue.content ?? '');
                valueContent = av === 0 ? childContent : valueContent + (childContent ?? '');
                if (attributeValue.subs) {
                    valueSubs = valueSubs.concat(attributeValue.subs as never[]);
                }
            }
        }
        if (attrName === '_props' && valueContent) {
            for (let propName in valueContent) {
                instance[propName] = valueContent[propName];
                instance._props[propName] = valueContent[propName];
            }
        } else {
            instance[attrName] = valueContent;
            instance._props[attrName] = valueContent;
        }
    }
}