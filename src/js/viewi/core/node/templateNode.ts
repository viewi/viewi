import { NodeType, NodeTypePacked } from "./nodeType"

export type TemplateNode = {
    t: NodeTypePacked,
    type: NodeType,
    c?: string,
    content?: string,
    code?: number,
    subs?: string[],
    e?: boolean,
    expression?: boolean,
    raw?: boolean,
    h?: TemplateNode[],
    children?: TemplateNode[],
    a?: TemplateNode[],
    attributes?: TemplateNode[],
    i?: TemplateNode[],
    slots?: TemplateNode[],
    directives?: TemplateNode[],
    unpacked?: boolean,
    dynamic?: TemplateNode,
    forData?: number,
    forItem?: string,
    forKey?: string,
    forKeyAuto?: boolean,
    func: Function,
    first?: boolean
}