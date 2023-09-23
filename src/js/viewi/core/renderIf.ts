import { BaseComponent } from "./BaseComponent";
import { TextAnchor } from "./anchor";
import { ContextScope } from "./contextScope";
import { ConditionalDirective, DirectiveMap } from "./directive";
import { dispose } from "./dispose";
import { TemplateNode } from "./node";
import { render } from "./render";

export function renderIf(
    instance: BaseComponent<any>,
    node: TemplateNode,
    scope: ContextScope,
    directive: TemplateNode,
    anchorNode: TextAnchor,
    ifConditions: ConditionalDirective,
    localDirectiveMap: DirectiveMap,
    index: number
) {
    let nextValue = true;
    for (let i = 0; i < index; i++) {
        nextValue = nextValue && !ifConditions.values[i];
    }
    if (directive.children) {
        nextValue = nextValue && !!(instance.$$t[
            directive.children[0].code!
        ](instance));
    }
    const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
    if (ifConditions.values[index] !== nextValue) {
        ifConditions.values[index] = nextValue;
        if (nextValue) {
            // render
            const scopeId = ++scope.parent!.counter;
            const nextScope: ContextScope = {
                id: scopeId,
                arguments: scope.parent!.arguments,
                components: scope.components,
                map: scope.parent!.map,
                track: scope.track,
                parent: scope.parent,
                children: {},
                counter: 0
            };
            scope.parent!.children[scopeId] = nextScope;
            render(anchorNode, instance, [node], nextScope, nextDirectives, false, true);
        } else {
            // remove and dispose
            dispose(scope, instance);
            while (anchorNode.previousSibling._anchor !== anchorNode._anchor) {
                anchorNode.previousSibling!.remove();
            }
        }
    }
}