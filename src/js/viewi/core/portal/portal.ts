import { getAnchor } from "../anchor/getAnchor";
import { TextAnchor } from "../anchor/textAnchor";
import { BaseComponent } from "../component/baseComponent";
import { ContextScope } from "../lifecycle/contextScope";
import { IDestroyable } from "../lifecycle/iDestroyable";
import { PropsContext } from "../lifecycle/propsContext";
import { HtmlNodeType } from "../node/htmlNodeType";
import { IRenderable } from "../render/iRenderable";
import { portals } from "./portals";
import { renderPortal } from "./renderPortal";

export class Portal extends BaseComponent<Portal> implements IRenderable, IDestroyable {
    to?: string;
    name?: string;
    _name: string = 'Portal';
    anchorNode?: TextAnchor;

    destroy(): void {
        if (this.to && this.anchorNode && this.anchorNode.previousSibling) {
            while (this.anchorNode.previousSibling._anchor !== this.anchorNode._anchor) {
                this.anchorNode.previousSibling!.remove();
            }
            this.anchorNode.previousSibling!.remove();
            this.anchorNode.remove();
        }
    }

    render(target: HtmlNodeType, name: string, scope: ContextScope, props?: PropsContext, hydrate = false, insert = false, params: { [key: string]: any } = {}): void {
        if (this.name) {
            const idEnd = 'portal_' + this.name + '_end';
            if (hydrate) {
                const portalEndMark = document.getElementById(idEnd);
                if (portalEndMark) {
                    const portalPositionIndex = Array.prototype.indexOf.call(target.childNodes, portalEndMark);
                    if (portalPositionIndex > 0) {
                        const anchor = getAnchor(target);
                        if (!(this.name in portals)) {
                            portals[this.name] = {};
                        }
                        portals[this.name].current = anchor.current + 1;
                        anchor.current = portalPositionIndex;
                        if (portals[this.name].queue) {
                            const queue = portals[this.name].queue;
                            for (let i = 0; i < queue.length; i++) {
                                queue[i][0].apply(null, queue[i][1]);
                            }
                            delete portals[this.name].queue;
                        }
                    }
                }
            } else {
                const idBegin = 'portal_' + this.name;
                const portalBeginElement = document.createElement('i');
                const portalEndElement = document.createElement('i');
                portalBeginElement.setAttribute('id', idBegin);
                portalEndElement.setAttribute('id', idEnd);
                const style = 'display: none !important;';
                portalBeginElement.setAttribute('style', style);
                portalEndElement.setAttribute('style', style);
                insert
                    ? target.parentElement!.insertBefore(portalBeginElement, target)
                    : target.appendChild(portalBeginElement);
                insert
                    ? target.parentElement!.insertBefore(portalEndElement, target)
                    : target.appendChild(portalEndElement);
            }
        } else if (this.to) {
            if (hydrate) {
                if (this.to in portals && portals[this.to].current) {
                    renderPortal(this, scope, hydrate, insert);
                } else {
                    const delayedRender = [renderPortal, [this, scope, hydrate, insert]];
                    if (this.to in portals) {
                        portals[this.to].queue.push(delayedRender);
                    } else {
                        portals[this.to] = {
                            queue: [delayedRender]
                        };
                    }
                }
            } else {
                renderPortal(this, scope, false, true);
            }
        } else {
            throw new Error("Portal component should have either 'name' or 'to' attribute.");
        }
    }
}