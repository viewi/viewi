import { Anchor } from "../anchor/anchor";
import { HtmlNodeType } from "../node/htmlNodeType";

export function hydrateRaw(vdom: HTMLElement, anchor: Anchor, target: HtmlNodeType) {
    if (vdom.childNodes.length > 0) {
        const invalid: number[] = [];
        const max = target.childNodes.length;
        const rawNodes: HTMLElement[] = Array.prototype.slice.call(vdom.childNodes);
        for (let rawNodeI = 0; rawNodeI < rawNodes.length; rawNodeI++) {
            const rawNode = rawNodes[rawNodeI];
            const rawNodeType = rawNode.nodeType;
            if (rawNodeType === 3) {
                // text
                const currentTargetNode = target.childNodes[anchor.current];
                if (currentTargetNode && currentTargetNode.nodeType === rawNodeType) {
                    currentTargetNode.nodeValue = rawNode.nodeValue;
                } else {
                    anchor.added++;
                    max > anchor.current && invalid.push(anchor.current + 1);
                    max > anchor.current
                        ? target.insertBefore(rawNode, target.childNodes[anchor.current])
                        : target.appendChild(rawNode);
                    // insert
                    //     ? target.parentElement!.insertBefore(rawNode, target)
                    //     : target.appendChild(rawNode);
                }
            } else {
                // other
                const currentTargetNode = target.childNodes[anchor.current];
                if (
                    !currentTargetNode
                    || currentTargetNode.nodeType !== rawNodeType
                    || (rawNodeType === 1 && currentTargetNode.nodeName !== rawNode.nodeName)
                ) {
                    anchor.added++;
                    max > anchor.current && invalid.push(anchor.current + 1);
                    max > anchor.current
                        ? target.insertBefore(rawNode, target.childNodes[anchor.current])
                        : target.appendChild(rawNode);
                    // mismatch by type
                    // insert
                    //     ? target.parentElement!.insertBefore(rawNode, target)
                    //     : target.appendChild(rawNode);
                } else if (rawNodeType === 1) {
                    if (currentTargetNode.nodeName !== rawNode.nodeName || (<HTMLElement>currentTargetNode).outerHTML !== rawNode.outerHTML) {
                        const keepKey = (<HTMLElement>currentTargetNode).getAttribute('data-keep');
                        if (!keepKey || keepKey !== rawNode.getAttribute('data-keep')) { // keep server-side version
                            (<HTMLElement>currentTargetNode).outerHTML = rawNode.outerHTML;
                        }
                    }
                }
                // matched, continue
            }
            anchor.current++;
        }
        if (invalid.length > 0) {
            anchor.invalid = anchor.invalid.concat(invalid);
        }
    }
}