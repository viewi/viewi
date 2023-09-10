import { BaseComponent } from "./BaseComponent";
import { TemplateNode } from "./node";
import { DataScope } from "./scope";

export function renderText(instance: BaseComponent<any>, node: TemplateNode, textNode: Text, scope?: DataScope) {
    let callArguments = [instance];
    if (scope) {
        callArguments = callArguments.concat(scope.arguments);
    }
    const content = node.expression
        ? instance.$$t[node.code as number].apply(null, callArguments)
        : (node.content ?? '');
    textNode.nodeValue !== content && (textNode.nodeValue = content);
};