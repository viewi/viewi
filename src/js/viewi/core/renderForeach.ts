import { BaseComponent } from "./BaseComponent";
import { TextAnchor, createAnchorNode } from "./anchor";
import { ArrayScope, ForeachAnchorEnum } from "./arrayScope";
import { DirectiveMap } from "./directive";
import { TemplateNode } from "./node";
import { render } from "./render";
import { DataScope } from "./scope";

export function renderForeach(
    instance: BaseComponent<any>,
    node: TemplateNode,
    directive: TemplateNode,
    anchorNode: TextAnchor,
    currentArrayScope: ArrayScope,
    localDirectiveMap: DirectiveMap,
    scope?: DataScope
) {
    let callArguments = [instance];
    if (scope) {
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
        let nextScope: DataScope = scope
            ? { map: { ...scope.map }, arguments: [...scope.arguments] }
            : { map: {}, arguments: [] };
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
            const itemBeginAnchor = createAnchorNode(insertTarget, true, undefined, ForeachAnchorEnum.BeginAnchor); // begin foreach item
            render(insertTarget, instance, [node], nextDirectives, false, true, nextScope);
            const itemEndAnchor = createAnchorNode(insertTarget, true, undefined, ForeachAnchorEnum.EndAnchor); // end foreach item
            if (dataKey in currentArrayScope) { // same key, different value
                deleteMap[dataKey] = currentArrayScope[dataKey];
            }
            currentArrayScope[dataKey] = {
                key: dataKey,
                value: dataItem,
                begin: itemBeginAnchor,
                end: itemEndAnchor
            };
        }
    }
    // removing what's missing
    for (let di in currentArrayScope) {
        if (!(di in usedMap)) {
            while (currentArrayScope[di].end.previousSibling._anchor !== ForeachAnchorEnum.BeginAnchor) {
                currentArrayScope[di].end.previousSibling!.remove();
            }
            currentArrayScope[di].begin.remove();
            currentArrayScope[di].end.remove();
            delete currentArrayScope[di];
        }
    }
    for (let di in deleteMap) {
        while (deleteMap[di].end.previousSibling._anchor !== ForeachAnchorEnum.BeginAnchor) {
            deleteMap[di].end.previousSibling!.remove();
        }
        deleteMap[di].begin.remove();
        deleteMap[di].end.remove();
    }
}