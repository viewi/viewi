import { MethodType } from "./methodType";

export class Request {
    url: string;
    method: MethodType;
    headers: { [name: string]: string } = {};
    body: any = null;
    isExternal: boolean = false;

    constructor(url: string, method: MethodType, headers: { [name: string]: string; } = {}, body: any = null) {
        this.url = url;
        this.method = method;
        this.headers = headers;
        this.body = body;
    }

    withMethod(method: MethodType) {
        const clone = this.clone();
        clone.method = method;
        return clone;
    }

    withUrl(url: string) {
        const clone = this.clone();
        clone.url = url;
        return clone;
    }

    withHeaders(headers: { [name: string]: string; }) {
        const clone = this.clone();
        clone.headers = { ...clone.headers, ...headers };
        return clone;
    }

    withHeader(name: string, value: any) {
        const clone = this.clone();
        clone.headers[name] = value;
        return clone;
    }

    withBody(body: any = null) {
        const clone = this.clone();
        clone.body = body;
        return clone;
    }

    clone() {
        const clone = new Request(this.url, this.method, this.headers, this.body);
        return clone;
    }

    // server-side only, makes no difference on front end
    markAsExternal() {
        return this;
    }
};