import { Router } from "../router/router";
import { ComponentMetaDataList } from "./componentMetaData";

export const componentsMeta: {
    list: ComponentMetaDataList,
    router: Router,
    config: { [key: string]: any },
    booleanAttributes: {},
    globals: { [key: string]: string }
} = {
    list: {},
    config: {},
    globals: {},
    booleanAttributes: {},
    router: new Router()
};