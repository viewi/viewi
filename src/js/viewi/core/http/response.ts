export class Response {
    url: string;
    status: number;
    statusText: string;
    headers: { [name: string]: string } = {};
    body: any = null;

    constructor(url: string, status: number, statusText: string, headers: { [name: string]: string } = {}, body: any = null) {
        this.url = url;
        this.status = status;
        this.statusText = statusText;
        this.headers = headers;
        this.body = body;
    }

    withUrl(url: any) {
        var clone = this.clone();
        clone.url = url;
        return clone;
    }

    withStatus(status: number, statusText: string | null = null) {
        var clone = this.clone();
        clone.status = status;
        if (statusText !== null) {
            clone.statusText = statusText;
        }
        return clone;
    }

    withHeaders(headers: { [name: string]: string }) {
        var clone = this.clone();
        clone.headers = { ...clone.headers, ...headers };
        return clone;
    }

    withHeader(name: string | number, value: any) {
        var clone = this.clone();
        clone.headers[name] = value;
        return clone;
    }

    withBody(body: any = null) {
        var clone = this.clone();
        clone.body = body;
        return clone;
    }

    ok() {
        return this.status >= 200 && this.status < 300;
    }

    clone() {
        var clone = new Response(this.url, this.status, this.statusText, this.headers, this.body);
        return clone;
    }
};