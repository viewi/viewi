import { TemplateNode } from "../node/templateNode"

export type ComponentMetaData = {
    nodes?: TemplateNode,
    dependencies?: any[],
    di?: 'Singleton' | 'Scoped' | 'Transient',
    base?: boolean,
    refs?: { [key: string]: boolean }
}

export type ComponentMetaDataList = {
    [key: string]: ComponentMetaData
}