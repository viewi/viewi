import { ContextScope } from "./contextScope";
import { TemplateNode } from "./node";

export type Slots = { [key: string]: { node: TemplateNode, scope: ContextScope } }