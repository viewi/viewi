import { ContextScope } from "../lifecycle/contextScope";
import { TemplateNode } from "./templateNode";

export type Slots = { [key: string]: { node: TemplateNode, scope: ContextScope } }