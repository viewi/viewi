import { TextAnchor } from "../anchor/textAnchor"
import { BaseComponent } from "../component/baseComponent"
import { ContextScope } from "../lifecycle/contextScope"
import { DIContainer } from "./diContainer"

type RenderIteration = {
    instance: BaseComponent<any>,
    scope: ContextScope,
    slots: { [key: string]: TextAnchor }
}

type GlobalScope = {
    hydrate: boolean,
    rootScope: ContextScope | false,
    scopedContainer: DIContainer,
    located: { [key: string]: boolean },
    iteration: { [key: string]: RenderIteration },
    lastIteration: { [key: string]: RenderIteration },
    layout: string,
    cancel: boolean
}

export const globalScope: GlobalScope = {
    hydrate: true, // first time hydrate, TODO: configurable, MFE won't need hydration
    rootScope: false,
    scopedContainer: {},
    located: {},
    iteration: {},
    lastIteration: {},
    layout: '',
    cancel: false
}