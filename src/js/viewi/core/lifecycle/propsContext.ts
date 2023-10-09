import { ContextScope } from "./contextScope"
import { TemplateNode } from "../node/templateNode"

export type PropsContext = {
    attributes: TemplateNode[]
    scope: ContextScope
}