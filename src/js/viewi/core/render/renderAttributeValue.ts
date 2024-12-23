import { BaseComponent } from "../component/baseComponent";
import { TemplateNode } from "../node/templateNode";
import { componentsMeta } from "../component/componentsMeta";
import { ContextScope } from "../lifecycle/contextScope";
import { HtmlNodeType } from "../node/htmlNodeType";
import { xLinkNs } from "../helpers/isSvg";

const SVG_NAMESPACES = {
    svg: 'http://www.w3.org/2000/svg',
    html: 'http://www.w3.org/1999/xhtml',
    xml: 'http://www.w3.org/XML/1998/namespace',
    xlink: 'http://www.w3.org/1999/xlink',
    xmlns: 'http://www.w3.org/2000/xmlns/'
}

export function renderAttributeValue(
    instance: BaseComponent<any>,
    attribute: TemplateNode,
    element: HTMLElement & HtmlNodeType,
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
        if (element.isSvg) {
            const parts = attrName.split(':', 2);
            const baseAttrName = parts[0];
            const targetName = parts.length > 1 ? parts[1] : attrName;
            const attrNS = baseAttrName in SVG_NAMESPACES ? SVG_NAMESPACES[baseAttrName] : null;
            try {
                if (valueContent !== null) {
                    if (valueContent !== element.getAttributeNS(attrNS, targetName)) {
                        element.setAttributeNS(attrNS, attrNS ? targetName : attrName, <string>valueContent);
                    }
                } else {
                    element.removeAttributeNS(attrNS, attrNS ? targetName : attrName);
                }
                // Debug Safari
                // document.getElementById('logs')!.innerHTML += ' *** Rendered ' + ' NS=' + attrNS + ' ' + attrName  + ' T=' + targetName + ' ' + element.nodeName + ' *** ';
            } catch (err) {
                console.error('Can not render namespace attribute', attrName, err);
                // Debug Safari
                // document.getElementById('logs')!.innerHTML += ' XXX Can not render namespace attribute ' + ' NS=' + attrNS + ' ' + attrName + ' T=' + targetName + ' ' + element.nodeName + ' XXX ';
            }
        } else {
            if (valueContent !== null) {
                valueContent !== element.getAttribute(attrName) && element.setAttribute(attrName, <string>valueContent);
            } else {
                element.removeAttribute(attrName);
            }
        }
    }
};