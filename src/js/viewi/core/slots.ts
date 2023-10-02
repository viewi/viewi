import { ContextScope } from "./contextScope";
import { TemplateNode } from "./templateNode";

export type Slots = { [key: string]: { node: TemplateNode, scope: ContextScope } }