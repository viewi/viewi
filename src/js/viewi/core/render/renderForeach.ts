import { BaseComponent } from "../component/baseComponent";
import { TemplateNode } from "../node/templateNode";
import { render } from "./render";
import { DirectiveMap } from "../directive/DirectiveMap";
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
    anchorNode: TextAnchor,
    currentArrayScope: ArrayScope,
    localDirectiveMap: DirectiveMap,
    scope: ContextScope
) {
    let callArguments = [instance];
    if (scope.arguments) {
        callArguments = callArguments.concat(scope.arguments);
    }
    const data = instance.$$t[
        directive.children![0].forData!
    ].apply(null, callArguments);

    const isNumeric = Array.isArray(data);
    let insertTarget = anchorNode;
    let between = false;
    const usedMap = {};
    const deleteMap: ArrayScope = {};
    for (let forKey in data) {
        const dataKey = isNumeric ? +forKey : forKey;
        const dataItem = data[dataKey];
        const scopeId = ++scope.counter;
        const nextScope: ContextScope = {
            id: scopeId,
            why: 'forItem',
            instance: instance,
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
        // if (!(dataKey in currentArrayScope)) { // if unique key provided
        // }
        let found = false;
        for (let di in currentArrayScope) {
            if (currentArrayScope[di] === dataItem) {
                found = true;
                between = false;
                insertTarget = anchorNode;
                break;
            } else if (!between && !(dataKey in usedMap)) {
                insertTarget = currentArrayScope[di].begin;
                between = true;
            }
        }
        usedMap[dataKey] = true;
        if (!found) {
            nextScope.map[directive.children![0].forKey!] = nextScope.arguments.length;
            nextScope.arguments.push(dataKey);
            nextScope.map[directive.children![0].forItem!] = nextScope.arguments.length;
            nextScope.arguments.push(dataItem);
            const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
            const itemBeginAnchor = createAnchorNode(insertTarget, true, undefined, ForeachAnchorEnum.BeginAnchor + nextAnchorNodeId()); // begin foreach item
            render(insertTarget, instance, [node], nextScope, nextDirectives, false, true);
            const itemEndAnchor = createAnchorNode(insertTarget, true, undefined, itemBeginAnchor._anchor); // end foreach item
            if (dataKey in currentArrayScope) { // same key, different value
                deleteMap[dataKey] = currentArrayScope[dataKey];
            }
            currentArrayScope[dataKey] = {
                key: dataKey,
                value: dataItem,
                begin: itemBeginAnchor,
                end: itemEndAnchor,
                scope: nextScope
            };
        }
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
    for (let di in deleteMap) {
        const endAnchor = deleteMap[di].end;
        while (endAnchor.previousSibling._anchor !== endAnchor._anchor) {
            endAnchor.previousSibling!.remove();
        }
        deleteMap[di].begin.remove();
        dispose(deleteMap[di].scope);
        endAnchor.remove();
    }
}