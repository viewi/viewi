import { Anchor } from "./anchor";
import { anchors } from "./anchors";
import { NodeAnchor } from "./nodeAnchor";

let anchorId = 0;

export function getAnchor(target: NodeAnchor): Anchor {
    if (!target.__aid) {
        target.__aid = ++anchorId;
        anchors[target.__aid] = { current: -1, target, invalid: [], added: 0 };
    }
    return anchors[target.__aid];
}