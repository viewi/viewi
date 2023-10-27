import { Router } from "../router/router";
import { ComponentMetaDataList } from "./componentMetaData";

export const componentsMeta: {
    list: ComponentMetaDataList,
    router: Router,
    config: { [key: string]: any },
    booleanAttributes: {}
} = {
    list: {},
    config: {},
    booleanAttributes: {},
    router: new Router()
};