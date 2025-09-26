import { BaseComponent } from "../component/baseComponent";
import { IStartUp } from "../component/iStartUp";
import { HttpClient } from "../http/httpClient";
import { DelayRender } from "../portal/delayRender";
import { Portal } from "../portal/portal";
import { factory } from "./factory";
import { register } from "./register";
import { resolve } from "./resolve";

export function setUp(startUpItems: string[]) {
    register['BaseComponent'] = BaseComponent;
    factory('HttpClient', HttpClient, () => new HttpClient());
    factory('Portal', Portal, () => new Portal());
    factory('DelayRender', DelayRender, () => new DelayRender());
    for (let i = 0; i < startUpItems.length; i++) {
        const startUpInstance = resolve(startUpItems[i]) as IStartUp;
        startUpInstance.setUp();
    }
}