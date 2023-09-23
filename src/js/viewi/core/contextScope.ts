import { BaseComponent } from "./BaseComponent";

export type ContextScope = {
    counter: number, // current id counter
    id: number, // unique per parent scope
    arguments: any[], // array (foreach directive) arguments
    map: { [key: string]: number }, // array (foreach directive) arguments positions
    components: BaseComponent<any>[], // disposable components
    track: { path: string, id: number }[], // disposable reactivity items
    parent?: ContextScope,
    children: { [key: string]: ContextScope } // all nested scopes from directives and components, tree disposal
}