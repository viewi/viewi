import { BaseComponent } from "../component/baseComponent";
import { TextAnchor } from "../anchor/textAnchor";
import { TemplateNode } from "../node/templateNode";
import { ContextScope } from "../lifecycle/contextScope";

export function renderRaw(instance: BaseComponent<any>, node: TemplateNode, scope: ContextScope, anchorNode: TextAnchor) {
    // remove
    while (anchorNode.previousSibling._anchor !== anchorNode._anchor) {
        anchorNode.previousSibling!.remove();
    }
    // insert new content
    const parentTagNode = anchorNode.parentElement!;
    const vdom = document.createElement(parentTagNode.nodeName);
    let callArguments = [instance];
    if (scope.arguments) {
        callArguments = callArguments.concat(scope.arguments);
    }
    const content = (node.expression
        ? instance.$$t[node.code as number].apply(null, callArguments)
        : node.content) ?? '';
    vdom.innerHTML = content;
    const rawNodes = Array.prototype.slice.call(vdom.childNodes);
    for (let rawNodeI = 0; rawNodeI < rawNodes.length; rawNodeI++) {
        const rawNode = rawNodes[rawNodeI];
        parentTagNode.insertBefore(rawNode, anchorNode);
    }
}