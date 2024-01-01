import { Resolver } from "../events/resolver";
import { getScopeState } from "../lifecycle/scopeState";
import { MethodType } from "./methodType";
import { runRequest } from "./runRequest";
import { Response } from "./response";
import { Request } from "./request";
import { resolve } from "../di/resolve";
import { IHttpInterceptor } from "./iHttpInterceptor";
import { IRequestHandler } from "./iRequestHandler";
import { IResponseHandler } from "./iResponseHandler";

const interceptResponses = function (response: Response, callback, interceptorInstances: IHttpInterceptor[]) {
    const total = interceptorInstances.length;
    let current = total;

    const lastCall = function (response: Response, keepGoing: boolean) {
        if (keepGoing && response.status >= 200 && response.status < 300) {
            callback(response.body);
        } else {
            callback(undefined, !!response.body ? response.body : 'Failed');
        }
    };

    const run = function (response: Response, keepGoing: boolean) {
        if (keepGoing) {
            if (current > -1) {
                const interceptor: IHttpInterceptor = interceptorInstances[current];
                interceptor.response(response, responseHandler);
            } else {
                lastCall(response, keepGoing);
            }
        } else {
            lastCall(response, keepGoing);
        }
    };

    const responseHandler: IResponseHandler = {
        next: function (response: Response) {
            current--;
            run(response, true);
        },
        reject: function (response: Response) {
            current--;
            run(response, false);
        }
    };
    responseHandler.next(response);
}

class HttpClient {
    interceptors: string[] = [];

    request(method: MethodType, url: string, body?: any, headers?: { [name: string]: string }) {
        const $this = this;
        const resolver = new Resolver(function (callback) {
            try {
                const state = getScopeState();
                const request = new Request(url, method, headers, body);
                let current = -1;
                const total = $this.interceptors.length;
                const interceptorInstances: IHttpInterceptor[] = [];
                const lastCall = function (request: Request, keepGoing: boolean) {
                    if (keepGoing) {
                        const requestKey = request.method + '_' + request.url + '_' + JSON.stringify(request.body);
                        if (requestKey in state.http) {
                            const responseData = JSON.parse(state.http[requestKey]);
                            delete state.http[requestKey];
                            const response = new Response(request.url, 200, 'OK', {}, responseData);
                            interceptResponses(response, callback, interceptorInstances);
                            return;
                        } else {
                            runRequest(function (response: Response) {
                                interceptResponses(response, callback, interceptorInstances);
                            }, request.method, request.url, request.body, request.headers);
                        }
                    } else {
                        const response = new Response(request.url, 0, 'Rejected', {}, null);
                        interceptResponses(response, callback, interceptorInstances);
                    }
                }
                const run = function (request: Request, keepGoing: boolean) {
                    if (!keepGoing) {
                        lastCall(request, keepGoing);
                        return;
                    }
                    if (current < total) {
                        const interceptor: IHttpInterceptor = resolve($this.interceptors[current]);
                        interceptorInstances.push(interceptor);
                        interceptor.request(request, requestHandler);
                    } else {
                        lastCall(request, keepGoing);
                    }
                };
                const requestHandler: IRequestHandler = {
                    next: function (request: Request): void {
                        current++;
                        run(request, true);
                    },
                    reject: function (request: Request): void {
                        current++;
                        run(request, false);
                    }
                };
                requestHandler.next(request);
            } catch (ex) {
                console.error(ex);
                callback(undefined, ex);
            }
        });

        return resolver;
    }

    get(url: string, headers?: { [name: string]: string }) {
        return this.request("get", url, null, headers);
    }

    post(url: string, body?: any, headers?: { [name: string]: string }) {
        return this.request("post", url, body, headers);
    }

    withInterceptor(interceptor: string) {
        const http = new HttpClient();
        http.interceptors = [...this.interceptors, interceptor];
        return http;
    }
}

export { HttpClient }