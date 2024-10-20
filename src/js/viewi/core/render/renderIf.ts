import { BaseComponent } from "../component/baseComponent";
import { TemplateNode } from "../node/templateNode";
import { render } from "./render";
import { ConditionalDirective } from "../directive/conditionalDirective";
import { DirectiveMap } from "../directive/directiveMap";
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
    const scope = scopeContainer.scope.parent!;
    if (!scope || scope.disposed) {
        return;
    }
    if (directive.children) {
        let callArguments = [instance];
        if (scope.arguments) {
            callArguments = callArguments.concat(scope.arguments);
        }
        nextValue = nextValue && !!(instance.$$t[
            directive.children[0].code!
        ].apply(null, callArguments));
    }
    const anchorNode = scopeContainer.anchorNode;
    const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
    if (ifConditions.values[index] !== nextValue) {
        ifConditions.values[index] = nextValue;
        if (nextValue) {
            // render
            const scopeId = ++scope.counter;
            const nextScope: ContextScope = {
                id: scopeId,
                iteration: scope.iteration,
                why: index === 0 ? 'if' : (directive.children ? 'elseif' : 'else'),
                instance: instance,
                lastComponent: scope.lastComponent,
                arguments: [...scope.arguments],
                map: { ...scope.map },
                track: [],
                parent: scope,
                children: {},
                counter: 0,
                slots: scope.slots
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
                iteration: scope.iteration,
                why: 'if',
                instance: instance,
                lastComponent: scope.lastComponent,
                arguments: [],
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