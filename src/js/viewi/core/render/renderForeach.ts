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
    for (let forKey in data) {
        let found = false;
        positionIndex++;
        const dataKey = isNumeric ? +forKey : forKey;
        const dataItem = data[dataKey];
        let foundIndex = -1;
        for (let di in currentArrayScope) {
            foundIndex++;
            const currentScopeItem = currentArrayScope[di];
            if (currentScopeItem.value === dataItem && (noKey || currentScopeItem.key === dataKey)) {
                found = true;
                if (foundIndex !== positionIndex) {
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
            currentArrayScope[dataKey] = {
                key: dataKey,
                value: dataItem,
                begin: itemBeginAnchor,
                end: itemEndAnchor,
                scope: nextScope
            };
        }
        usedMap[dataKey] = true;
    }
    // removing what's missing
    for (let di in currentArrayScope) {
        if (!(di in usedMap)) {
            const endAnchor = currentArrayScope[di].end;
            while (endAnchor.previousSibling._anchor !== endAnchor._anchor) {
                endAnchor.previousSibling!.remove();
            }
            currentArrayScope[di].begin.remove();
            endAnchor.remove();
            dispose(currentArrayScope[di].scope);
            delete currentArrayScope[di];
        }
    }
}