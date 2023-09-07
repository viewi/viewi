import { BaseComponent } from "./BaseComponent";
import { TextAnchor } from "./anchor";
import { ConditionalDirective, DirectiveMap } from "./directive";
import { TemplateNode } from "./node";
import { render } from "./render";

export function renderIf(
    instance: BaseComponent<any>,
    node: TemplateNode,
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
    if (ifConditions.values[index] !== nextValue) {
        ifConditions.values[index] = nextValue;
        if (nextValue) {
            // render
            render(anchorNode, instance, [node], { ...localDirectiveMap }, false, true);
        } else {
            // remove and dispose
            while (!anchorNode.previousSibling._anchor) {
                anchorNode.previousSibling!.remove();
            }
        }
    }
}