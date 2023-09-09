import { getAnchor } from "./anchor";

export function hydrateTag(target: Node, tag: string): Node {
    const anchor = getAnchor(target);
    const max = target.childNodes.length;
    let end = anchor.current + 3;
    end = end > max ? max : end;
    const invalid: number[] = [];
    for (let i = anchor.current + 1; i < end; i++) {
        const potentialNode = target.childNodes[i];
        if (
            potentialNode.nodeType === 1
            && potentialNode.nodeName.toLowerCase() === tag
        ) {
            anchor.current = i;
            anchor.invalid = anchor.invalid.concat(invalid);
            // console.log('Hydrate match', potentialNode);
            return potentialNode as Node;
        }
        invalid.push(i);
    }
    anchor.added++;
    anchor.invalid = anchor.invalid.concat(invalid);
    console.log('Hydrate not found', tag);
    const element = document.createElement(tag);
    anchor.current = anchor.current + invalid.length + 1;
    return max > anchor.current
        ? target.insertBefore(element, target.childNodes[anchor.current])
        : target.appendChild(element);
}