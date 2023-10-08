import { BaseComponent } from "./component/baseComponent";
import { TemplateNode } from "./templateNode";

export function updateComment(instance: BaseComponent<any>, node: TemplateNode, commentNode: Comment) {
    const content = node.expression
        ? instance.$$t[node.code as number](instance)
        : (node.content ?? '');
        commentNode.nodeValue !== content && (commentNode.nodeValue = content);
};