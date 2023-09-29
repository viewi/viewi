import { BaseComponent } from "./BaseComponent"
import { ContextScope } from "./contextScope"
import { TemplateNode } from "./node"

export type PropsContext = {
    attributes: TemplateNode[]
    scope: ContextScope,
    instance: BaseComponent<any>
}