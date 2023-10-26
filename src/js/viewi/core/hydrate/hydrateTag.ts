import { getAnchor } from "../anchor/getAnchor";
import { HtmlNodeType } from "../node/htmlNodeType";

const specialTags = { body: true, head: true, html: true };

export function hydrateTag(target: HtmlNodeType, tag: string): HtmlNodeType {
    const anchor = getAnchor(target);
    const max = target.childNodes.length;
    let end = anchor.current + 3;
    end = end > max ? max : end;
    const invalid: number[] = [];
    for (let i = anchor.current + 1; i < end; i++) {
        const potentialNode = target.childNodes[i];
        if (
            potentialNode.nodeType === 1
            && potentialNode.nodeName.toLowerCase() === tag.toLowerCase()
        ) {
            anchor.current = i;
            anchor.invalid = anchor.invalid.concat(invalid);
            // console.log('Hydrate match', potentialNode);
            return potentialNode as Node;
        }
        invalid.push(i);
    }
    if (tag in specialTags) {
        const nodes = document.getElementsByTagName(tag);
        if (nodes.length > 0) {
            anchor.invalid = [];
            return nodes[0];
        }
    }
    anchor.added++;
    anchor.invalid = anchor.invalid.concat(invalid);
    console.warn('Hydrate not found', tag);
    const element = document.createElement(tag);
    anchor.current = anchor.current + invalid.length + 1;
    return max > anchor.current
        ? target.insertBefore(element, target.childNodes[anchor.current])
        : target.appendChild(element);
}