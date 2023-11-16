import { BaseComponent } from "../component/baseComponent";
import { Process } from "../environment/process";
import { HttpClient } from "../http/httpClient";
import { factory } from "./factory";
import { register } from "./register";

export function setUp() {
    register['BaseComponent'] = BaseComponent;
    factory('HttpClient', HttpClient, () => new HttpClient());
    factory('Process', Process, () => new Process());
}