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
    unpacked?: boolean,
    func: Function
}

export type ComponentMeta = {
    [key: string]: {
        nodes?: TemplateNode
    }
}