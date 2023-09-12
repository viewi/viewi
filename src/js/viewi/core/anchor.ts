export type Anchor = {
    target: Node,
    current: number,
    added: number,
    invalid: number[]
}

let anchorId = 0;
let anchorNodeId = 0;
export const anchors: { [key: string]: Anchor } = {};

export type TextAnchor = Text & { _anchor?: string, previousSibling: (ChildNode & TextAnchor) };

export type NodeAnchor = Node & { __aid?: number };

export function getAnchor(target: NodeAnchor): Anchor {
    if (!target.__aid) {
        target.__aid = ++anchorId;
        anchors[target.__aid] = { current: -1, target, invalid: [], added: 0 };
    }
    return anchors[target.__aid];
}

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