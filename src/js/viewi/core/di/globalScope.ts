import { TextAnchor } from "../anchor/textAnchor"
import { BaseComponent } from "../component/baseComponent"
import { ContextScope } from "../lifecycle/contextScope"

type RenderIteration = {
    instance: BaseComponent<any>,
    scope: ContextScope,
    slots: { [key: string]: TextAnchor }
}

type GlobalScope = {
    hydrate: boolean,
    rootScope: ContextScope | false,
    located: { [key: string]: boolean },
    iteration: { [key: string]: RenderIteration },
    lastIteration: { [key: string]: RenderIteration },
    layout: string
}

export const globalScope: GlobalScope = {
    hydrate: true, // first time hydrate, TODO: configurable, MFE won't need hydration
    rootScope: false,
    located: {},
    iteration: {},
    lastIteration: {},
    layout: ''
}