import { BaseComponent } from "../component/baseComponent";
import { TemplateNode } from "../node/templateNode";
import { render } from "./render";
import { DirectiveMap } from "../directive/directiveMap";
import { createAnchorNode, nextAnchorNodeId } from "../anchor/createAnchorNode";
import { TextAnchor } from "../anchor/textAnchor";
import { ForeachAnchorEnum } from "../anchor/foreachAnchorEnum";
import { ContextScope } from "../lifecycle/contextScope";
import { ArrayScope } from "../lifecycle/arrayScope";
import { dispose } from "../lifecycle/dispose";

export function renderForeach(
    instance: BaseComponent<any>,
    node: TemplateNode,
    directive: TemplateNode,
    anchors: { anchorBegin: TextAnchor, anchorNode: TextAnchor },
    currentArrayScope: ArrayScope,
    localDirectiveMap: DirectiveMap,
    scope: ContextScope
) {
    let callArguments = [instance];
    if (scope.arguments) {
        callArguments = callArguments.concat(scope.arguments);
    }
    const forMeta = directive.children![0];
    const noKey = !!forMeta.forKeyAuto;
    const data = instance.$$t[
        forMeta.forData!
    ].apply(null, callArguments);

    const isNumeric = Array.isArray(data);
    const usedMap = {};
    let positionIndex = -1;
    let moveBefore = anchors.anchorBegin.nextSibling;
    const nextArrayScope: ArrayScope = { data: {} };
    for (let forKey in data) {
        let found = false;
        positionIndex++;
        const dataKey = isNumeric ? +forKey : forKey;
        const dataItem = data[dataKey];
        let foundIndex = -1;
        for (let di in currentArrayScope.data) {
            foundIndex++;
            if (di in usedMap) {
                continue;
            }
            const currentScopeItem = currentArrayScope.data[di];
            if (currentScopeItem.value === dataItem && (noKey || currentScopeItem.key === dataKey)) {
                found = true;
                usedMap[di] = true;
                nextArrayScope.data[dataKey] = currentScopeItem;
                if (foundIndex !== positionIndex && moveBefore !== currentScopeItem.begin) {
                    // move html
                    const beginAnchor = currentScopeItem.begin;
                    let nextToMove = beginAnchor.nextSibling;
                    moveBefore.before(beginAnchor);
                    while (nextToMove._anchor !== beginAnchor._anchor) {
                        nextToMove = nextToMove.nextSibling;
                        moveBefore.before(nextToMove.previousSibling);
                    }
                    moveBefore.before(nextToMove);
                }
                moveBefore = currentScopeItem.end.nextSibling;
                break;
            }
        }
        if (!found) {
            const scopeId = ++scope.counter;
            const nextScope: ContextScope = {
                id: scopeId,
                why: 'forItem',
                instance: instance,
                lastComponent: scope.lastComponent,
                arguments: [...scope.arguments],
                map: { ...scope.map },
                track: [],
                parent: scope,
                children: {},
                counter: 0
            };
            if (scope.refs) {
                nextScope.refs = scope.refs;
            }
            scope.children[scopeId] = nextScope;
            nextScope.map[directive.children![0].forKey!] = nextScope.arguments.length;
            nextScope.arguments.push(dataKey);
            nextScope.map[directive.children![0].forItem!] = nextScope.arguments.length;
            nextScope.arguments.push(dataItem);
            const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
            const itemBeginAnchor = createAnchorNode(moveBefore, true, undefined, ForeachAnchorEnum.BeginAnchor + nextAnchorNodeId()); // begin foreach item
            render(moveBefore, instance, [node], nextScope, nextDirectives, false, true);
            const itemEndAnchor = createAnchorNode(moveBefore, true, undefined, itemBeginAnchor._anchor); // end foreach item
            moveBefore = itemEndAnchor.nextSibling;
            nextArrayScope.data[dataKey] = {
                key: dataKey,
                value: dataItem,
                begin: itemBeginAnchor,
                end: itemEndAnchor,
                scope: nextScope
            };
        }
    }
    // removing what's missing
    for (let di in currentArrayScope.data) {
        if (!(di in usedMap)) {
            const endAnchor = currentArrayScope.data[di].end;
            while (endAnchor.previousSibling._anchor !== endAnchor._anchor) {
                endAnchor.previousSibling!.remove();
            }
            currentArrayScope.data[di].begin.remove();
            endAnchor.remove();
            dispose(currentArrayScope.data[di].scope);
            delete currentArrayScope.data[di];
        }
    }
    currentArrayScope.data = nextArrayScope.data;
}