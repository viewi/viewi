import { BaseComponent } from "../component/baseComponent";
import { IStartUp } from "../component/iStartUp";
import { Platform } from "../environment/platform";
import { HttpClient } from "../http/httpClient";
import { Portal } from "../portal/portal";
import { factory } from "./factory";
import { register } from "./register";
import { resolve } from "./resolve";

export function setUp(startUpItems: string[]) {
    register['BaseComponent'] = BaseComponent;
    factory('HttpClient', HttpClient, () => new HttpClient());
    factory('Platform', Platform, () => new Platform());
    factory('Portal', Portal, () => new Portal());
    for (let i = 0; i < startUpItems.length; i++) {
        const stratUpInstance = resolve(startUpItems[i]) as IStartUp;
        stratUpInstance.setUp();
    }
}