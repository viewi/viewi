import { ContextScope } from "./contextScope"
import { TemplateNode } from "./templateNode"

export type PropsContext = {
    attributes: TemplateNode[]
    scope: ContextScope
}