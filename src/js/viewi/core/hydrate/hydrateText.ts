import { BaseComponent } from "../component/baseComponent";
import { TemplateNode } from "../node/templateNode";
import { getAnchor } from "../anchor/getAnchor";
import { renderText } from "../render/renderText";
import { ContextScope } from "../lifecycle/contextScope";
import { HtmlNodeType } from "../node/htmlNodeType";

export function hydrateText(target: HtmlNodeType, instance: BaseComponent<any>, node: TemplateNode, scope: ContextScope): Text {
    const anchor = getAnchor(target);
    const max = target.childNodes.length;
    let end = anchor.current + 3;
    end = end > max ? max : end;
    const invalid: number[] = [];
    const start = anchor.current > -1 ? anchor.current : anchor.current + 1;
    for (let i = start; i < end; i++) {
        const potentialNode = target.childNodes[i];
        if (
            potentialNode.nodeType === 3
        ) {
            if (i === anchor.current) {
                // text after text, no shift
                break;
            }
            anchor.current = i;
            anchor.invalid = anchor.invalid.concat(invalid);
            renderText(instance, node, potentialNode as Text, scope);
            // console.log('Hydrate match', potentialNode);
            return potentialNode as Text;
        }
        i !== anchor.current && invalid.push(i);
    }
    anchor.added++;
    anchor.invalid = anchor.invalid.concat(invalid);
    const textNode = document.createTextNode('');
    renderText(instance, node, textNode, scope);
    anchor.current = anchor.current + invalid.length + 1;
    // console.log('Hydrate not found', textNode);
    return max > anchor.current
        ? target.insertBefore(textNode, target.childNodes[anchor.current])
        : target.appendChild(textNode);
}