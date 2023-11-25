import { ContextScope } from "../lifecycle/contextScope";
import { PropsContext } from "../lifecycle/propsContext";
import { HtmlNodeType } from "../node/htmlNodeType";

export interface IRenderable {
    render(target: HtmlNodeType, name: string, scope: ContextScope, props: PropsContext | undefined, hydrate: boolean, insert: boolean, params: { [key: string]: any }): void;
}