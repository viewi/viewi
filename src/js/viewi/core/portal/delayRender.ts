import { getAnchor } from "../anchor/getAnchor";
import { BaseComponent } from "../component/baseComponent";
import { ContextScope } from "../lifecycle/contextScope";
import { IDestroyable } from "../lifecycle/iDestroyable";
import { PropsContext } from "../lifecycle/propsContext";
import { HtmlNodeType } from "../node/htmlNodeType";
import { unpack } from "../node/unpack";
import { IRenderable } from "../render/iRenderable";
import { render } from "../render/render";
import { delayRenderQueue } from "./delayRenderQueue";

export class DelayRender extends BaseComponent<DelayRender> implements IRenderable, IDestroyable {
    _name: string = 'DelayRender';

    destroy(): void {
    }

    render(target: HtmlNodeType, name: string, scope: ContextScope, props?: PropsContext, hydrate = false, insert = false, params: { [key: string]: any } = {}): void {
        const root = scope.slots!.default.node;
        if (!root.unpacked) {
            unpack(root);
            root.unpacked = true;
        }
        // find <meta data-delayed>
        const anchor = getAnchor(target);
        const max = target.childNodes.length;
        let end = anchor.current + 3;
        end = end > max ? max : end;
        let delayedCurrent = anchor.current;
        for (let i = anchor.current + 1; i < end; i++) {
            const potentialNode = target.childNodes[i] as HTMLElement;
            if (
                potentialNode.nodeType === 1
                && potentialNode.nodeName.toLowerCase() === 'meta'
                && potentialNode.hasAttribute('data-delayed-start')
            ) {
                // data-delayed-start
                delayedCurrent = i + 1;
                // data-delayed-end
                while (i < max) {
                    const potentialNode = target.childNodes[i] as HTMLElement;
                    if (
                        potentialNode.nodeType === 1
                        && potentialNode.nodeName.toLowerCase() === 'meta'
                        && potentialNode.hasAttribute('data-delayed-end')
                    ) {
                        anchor.current = i;
                        break;
                    }
                    i++;
                }
                break;
            }
        }
        delayRenderQueue.push(function () {
            const anchor = getAnchor(target);
            const prevCurrent = anchor.current;
            anchor.current = delayedCurrent;
            render(target, scope.slots!.default.scope.instance, root.children!, scope.slots!.default.scope, undefined, hydrate, insert);
            anchor.current = prevCurrent;
        });
    }
}