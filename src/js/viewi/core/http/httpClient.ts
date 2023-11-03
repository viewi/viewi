import { Resolver } from "../events/resolver";
import { getScopeState } from "../lifecycle/scopeState";
import { MethodType } from "./methodType";
import { request } from "./request";
import { Response } from "./response";

class HttpClient {
    interceptors: string[] = [];

    request(method: MethodType, url: string, body?: any, headers?: { [name: string]: string | string[] }) {
        const resolver = new Resolver(function (callback) {
            try {
                const state = getScopeState();
                const requestKey = method.toLowerCase() + '_' + url + '_' + JSON.stringify(body);
                if (requestKey in state.http) {
                    callback(JSON.parse(state.http[requestKey]));
                    delete state.http[requestKey];
                    return;
                }
                request(function (response: Response) {
                    if (response.status === 0 || (response.status >= 200 && response.status < 400)) {
                        callback(response.data);
                    } else {
                        callback(undefined, response.data);
                    }
                }, method, url, body, headers);
            } catch (ex) {
                callback(undefined, ex);
            }
        });

        return resolver;
    }

    get(url: string, headers?: { [name: string]: string | string[] }) {
        return this.request("get", url, null, headers);
    }

    withInterceptor(interceptor: string) {
        const http = new HttpClient();
        http.interceptors = [...this.interceptors, interceptor];
        return http;
    }
}

export { HttpClient }