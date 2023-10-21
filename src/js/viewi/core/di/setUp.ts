import { HttpClient } from "../http/httpClient";
import { factory } from "./factory";

export function setUp() {
    factory('HttpClient', HttpClient, () => new HttpClient());
}