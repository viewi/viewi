export type NodeType = 'tag' | 'attr' | 'value' | 'component' | 'text' | 'comment' | 'root' | undefined;
export type NodeTypePacked = 't' | 'a' | 'v' | 'x' | 'm' | 'c' | 'r' | undefined;

export type TemplateNode = {
    t: NodeTypePacked,
    type: NodeType,
    c?: string,
    content?: string,
    code?: number,
    subs?: any[],
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
    func: Function
}

export type ComponentMeta = {
    [key: string]: {
        nodes?: TemplateNode
    }
}