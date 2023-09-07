export type Anchor = {
    target: Node,
    current: number,
    added: number,
    invalid: number[]
}

let anchorId = 0;
export const anchors: { [key: string]: Anchor } = {};

export type TextAnchor = Text & { _anchor?: boolean, previousSibling: (ChildNode & TextAnchor) };

export type NodeAnchor = Node & { __aid?: number };

export function getAnchor(target: NodeAnchor): Anchor {
    if (!target.__aid) {
        target.__aid = ++anchorId;
        anchors[target.__aid] = { current: -1, target, invalid: [], added: 0 };
    }
    return anchors[target.__aid];
}

export function createAnchorNode(anchor: Anchor, target: NodeAnchor): TextAnchor {
    const anchorNode = document.createTextNode('') as TextAnchor;
    anchorNode._anchor = true;
    anchor.current++;
    target.childNodes.length > anchor.current
        ? target.insertBefore(anchorNode, target.childNodes[anchor.current])
        : target.appendChild(anchorNode);
    return anchorNode;
}