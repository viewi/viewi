import { Router } from "../router/router";
import { ComponentMetaDataList } from "./componentMetaData";

export const componentsMeta: {
    list: ComponentMetaDataList,
    router: Router,
    booleanAttributes: {}
} = {
    list: {},
    booleanAttributes: {},
    router: new Router()
};