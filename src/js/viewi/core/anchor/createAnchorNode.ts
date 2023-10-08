import { Anchor } from "./anchor";
import { NodeAnchor } from "./nodeAnchor";
import { TextAnchor } from "./textAnchor";

let anchorNodeId = 0;

export function nextAnchorNodeId(): number {
    return ++anchorNodeId;
}

export function createAnchorNode(target: NodeAnchor, insert: boolean = false, anchor?: Anchor, name?: string): TextAnchor {
    const anchorNode = document.createTextNode('') as TextAnchor;
    anchorNode._anchor = name ?? ('#' + ++anchorNodeId);
    if (anchor) {
        anchor.current++;
    }
    (insert || (anchor && target.childNodes.length > anchor.current))
        ? (anchor ? target : target.parentElement)!.insertBefore(anchorNode, anchor ? target.childNodes[anchor.current] : target)
        : target.appendChild(anchorNode);
    return anchorNode;
}