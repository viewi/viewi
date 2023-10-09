import { BaseComponent } from "../component/baseComponent";
import { TemplateNode } from "../node/templateNode";
import { render } from "./render";
import { ConditionalDirective } from "../directive/conditionalDirective";
import { DirectiveMap } from "../directive/DirectiveMap";
import { TextAnchor } from "../anchor/textAnchor";
import { ContextScope } from "../lifecycle/contextScope";
import { dispose } from "../lifecycle/dispose";

export function renderIf(
    instance: BaseComponent<any>,
    node: TemplateNode,
    scopeContainer: { scope: ContextScope, anchorNode: TextAnchor },
    directive: TemplateNode,
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
    const anchorNode = scopeContainer.anchorNode;
    const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
    if (ifConditions.values[index] !== nextValue) {
        const scope = scopeContainer.scope.parent!;
        ifConditions.values[index] = nextValue;
        if (nextValue) {
            // render
            const scopeId = ++scope.counter;
            const nextScope: ContextScope = {
                id: scopeId,
                instance: instance,
                arguments: [...scope.arguments],
                components: [],
                map: { ...scope.map },
                track: [],
                parent: scope,
                children: {},
                counter: 0
            };
            if (scope.refs) {
                nextScope.refs = scope.refs;
            }
            scopeContainer.scope = nextScope;
            scope.children[scopeId] = nextScope;
            render(anchorNode, instance, [node], nextScope, nextDirectives, false, true);
        } else {
            // remove and dispose
            dispose(scopeContainer.scope);
            scopeContainer.scope = {
                id: -1,
                instance: instance,
                arguments: [],
                components: [],
                map: {},
                track: [],
                parent: scope,
                children: {},
                counter: 0
            };
            while (anchorNode.previousSibling._anchor !== anchorNode._anchor) {
                anchorNode.previousSibling!.remove();
            }
        }
    }
}