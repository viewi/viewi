import { BaseComponent } from "./BaseComponent";
import { TemplateNode } from "./templateNode";
import { ContextScope } from "./contextScope";

export function renderText(instance: BaseComponent<any>, node: TemplateNode, textNode: Text, scope: ContextScope) {
    let callArguments = [instance];
    if (scope.arguments) {
        callArguments = callArguments.concat(scope.arguments);
    }
    const content = (node.expression
        ? instance.$$t[node.code as number].apply(null, callArguments)
        : node.content) ?? '';
    textNode.nodeValue !== content && (textNode.nodeValue = content);
    // debug purposes, TODO: debug/dev mode logs
    // if (textNode.parentNode && !document.body.contains(textNode)) {
    //     console.log('Element is missing from the page', textNode);
    // }
};