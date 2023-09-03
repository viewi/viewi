import { BaseComponent } from "./BaseComponent";
import { TemplateNode } from "./node";

export function updateComment(instance: BaseComponent<any>, node: TemplateNode, commentNode: Comment) {
    const content = node.expression
        ? instance.$$t[node.code as number](instance)
        : (node.content ?? '');
        commentNode.nodeValue !== content && (commentNode.nodeValue = content);
};