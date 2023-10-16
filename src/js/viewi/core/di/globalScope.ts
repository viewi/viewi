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
    located: { [key: string]: boolean },
    iteration: { [key: string]: RenderIteration },
    lastIteration: { [key: string]: RenderIteration }
}

export const globalScope: GlobalScope = {
    hydrate: true, // first time hydrate, TODO: configurable, MFE won't need hydration
    located: {},
    iteration: {},
    lastIteration: {}
}