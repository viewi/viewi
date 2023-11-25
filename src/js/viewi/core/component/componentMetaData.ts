import { TemplateNode } from "../node/templateNode"

export type ComponentMetaData = {
    nodes?: TemplateNode,
    dependencies?: any[],
    di?: 'Singleton' | 'Scoped' | 'Transient',
    base?: boolean,
    custom?: boolean,
    renderer?: boolean,
    refs?: { [key: string]: boolean },
    parent?: string,
    lazy?: string,
    middleware?: string[],
    hooks?: {
        init?: boolean,
        mount?: boolean,
        mounted?: boolean,
        rendered?: boolean,
        destroy?: boolean
    }
}

export type ComponentMetaDataList = {
    [key: string]: ComponentMetaData
}