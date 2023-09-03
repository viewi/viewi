import { BaseComponent } from "./BaseComponent";
import { TemplateNode } from "./node";

export function renderText(instance: BaseComponent<any>, node: TemplateNode, textNode: Text) {
    const content = node.expression
        ? instance.$$t[node.code as number](instance)
        : (node.content ?? '');
    textNode.nodeValue !== content && (textNode.nodeValue = content);
};