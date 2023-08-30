export type NodeType = 'tag' | 'attr' | 'value' | 'component' | 'text' | 'comment' | undefined;
export type NodeTypePacked = 't' | 'a' | 'v' | 'x' | 'm' | 'c' | undefined;

export type Node = {
    t: NodeTypePacked,
    type: NodeType,
    c?: string,
    content?: string,
    code?: number,
    subs?: any[],
    e?: boolean,
    expression?: boolean,
    raw?: boolean,
    h?: Node[],
    children?: Node[],
    a?: Node[],
    attributes?: Node[],
    unpacked?: boolean,
    func: Function
}

export type ComponentMeta = {
    [key: string]: {
        nodes?: Node
    }
}