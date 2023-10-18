import { BaseComponent } from "../component/baseComponent";
import { Slots } from "../node/slots";

export type ContextScope = {
    counter: number, // current id counter
    id: number, // unique per parent scope
    why: ContextReason,
    instance: BaseComponent<any>,
    main?: boolean, // first scope of the instance and should be disposed
    arguments: any[], // array (foreach directive) arguments
    map: { [key: string]: number }, // array (foreach directive) arguments positions
    track: { path: string, id: number }[], // disposable reactivity items
    parent?: ContextScope,
    children: { [key: string]: ContextScope }, // all nested scopes from directives and components, tree disposal
    slots?: Slots,
    refs?: { [key: string]: boolean },
    keep?: boolean // do not dispose
}

export type ContextReason = 'if' | 'elseif' | 'else' | 'foreach' | 'forItem' | 'dynamic' | 'slot' | 'component' | string