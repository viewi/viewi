import { ContextScope } from "./contextScope"
import { TemplateNode } from "./node"

export type PropsContext = {
    attributes: TemplateNode[]
    scope: ContextScope
}