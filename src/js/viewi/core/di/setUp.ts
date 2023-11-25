import { BaseComponent } from "../component/baseComponent";
import { Platform } from "../environment/platform";
import { HttpClient } from "../http/httpClient";
import { Portal } from "../portal/portal";
import { factory } from "./factory";
import { register } from "./register";

export function setUp() {
    register['BaseComponent'] = BaseComponent;
    factory('HttpClient', HttpClient, () => new HttpClient());
    factory('Platform', Platform, () => new Platform());
    factory('Portal', Portal, () => new Portal());
}