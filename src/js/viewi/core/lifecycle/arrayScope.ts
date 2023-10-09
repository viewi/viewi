import { TextAnchor } from "../anchor/textAnchor";
import { ContextScope } from "./contextScope";

export type ArrayScope = { [key: string | number]: { key: string | number, value: any, begin: TextAnchor, end: TextAnchor, scope: ContextScope } };
