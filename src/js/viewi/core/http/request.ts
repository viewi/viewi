import { MethodType } from "./methodType";

export class Request {
    url: string;
    method: MethodType;
    headers: { [name: string]: string } = {};
    body: any = null;
    isExternal: boolean;

    constructor(url: string, method: MethodType, headers: { [name: string]: string; } = {}, body: any = null) {
        this.url = url;
        this.method = method;
        this.headers = headers;
        this.body = body;
    }

    withMethod(method: MethodType) {
        var clone = this.clone();
        clone.method = method;
        return clone;
    }

    withUrl(url: string) {
        var clone = this.clone();
        clone.url = url;
        return clone;
    }

    withHeaders(headers: { [name: string]: string; }) {
        var clone = this.clone();
        clone.headers = { ...clone.headers, ...headers };
        return clone;
    }

    withHeader(name: string, value: any) {
        var clone = this.clone();
        clone.headers[name] = value;
        return clone;
    }

    withBody(body: any = null) {
        var clone = this.clone();
        clone.body = body;
        return clone;
    }

    clone() {
        var clone = new Request(this.url, this.method, this.headers, this.body);
        return clone;
    }

    // server-side only, makes no difference on front end
    markAsExternal() {
        return this;
    }
};