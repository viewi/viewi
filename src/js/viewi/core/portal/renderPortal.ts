import { createAnchorNode } from "../anchor/createAnchorNode";
import { getAnchor } from "../anchor/getAnchor";
import { globalScope } from "../di/globalScope";
import { ContextScope } from "../lifecycle/contextScope";
import { unpack } from "../node/unpack";
import { render } from "../render/render";
import { Portal } from "./portal";
import { portals } from "./portals";

export function renderPortal(portal: Portal, scope: ContextScope, hydrate = false, insert = false) {
    const portalEndMark = document.getElementById('portal_' + portal.to! + '_end');
    if (portalEndMark) {
        const portalAnchorCurrent = portals[portal.to!].current;
        const renderTarget = insert ? portalEndMark : portalEndMark.parentElement!;
        const anchor = hydrate ? getAnchor(renderTarget) : undefined;
        const anchorCurrent = hydrate ? anchor!.current : 0;
        const portalPositionIndexBefore = Array.prototype.indexOf.call(renderTarget.childNodes, portalEndMark);
        hydrate && (anchor!.current = portalAnchorCurrent);
        // render
        let slotName: string = 'default';
        const anchorSlotBegin = createAnchorNode(renderTarget, insert, anchor); // begin slot
        if (slotName in scope.slots!) { // slot from parent
            const slot = scope.slots![slotName];
            if (!slot.node.unpacked) {
                unpack(slot.node);
                slot.node.unpacked = true;
            }
            render(renderTarget, slot.scope.instance, slot.node.children!, slot.scope, undefined, hydrate, insert);
        }
        const anchorSlotNode = createAnchorNode(renderTarget, insert, anchor, anchorSlotBegin!._anchor); // end slot
        if (scope.instance._name in globalScope.iteration) {
            globalScope.iteration[scope.instance._name].slots[slotName] = anchorSlotNode;
        }
        portal.anchorNode = anchorSlotNode;
        // restore anchor position
        if (hydrate) {
            portals[portal.to!].current = anchor!.current;
            const portalPositionIndexAfter = Array.prototype.indexOf.call(renderTarget.childNodes, portalEndMark);
            anchor!.current = anchorCurrent + portalPositionIndexAfter - portalPositionIndexBefore;
        }
    }
}