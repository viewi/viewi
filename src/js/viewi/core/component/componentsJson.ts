import { RouteItem } from "../router/routeItem"
import { ComponentMetaDataList } from "./componentMetaData"

export type ComponentsJson = {
    _meta: { boolean: string },
    _routes: RouteItem[],
    _config: { [key: string]: any }
} & ComponentMetaDataList;