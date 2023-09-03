export type Anchor = {
    target: HTMLElement,
    current: number,
    added: number,
    invalid: number[]
}

let anchorId = 0;
export const anchors: { [key: string]: Anchor } = {};

export function getAnchor(target: HTMLElement & { __aid?: number }): Anchor {
    if (!target.__aid) {
        target.__aid = ++anchorId;
        anchors[target.__aid] = { current: -1, target, invalid: [], added: 0 };
    }
    return anchors[target.__aid];
}