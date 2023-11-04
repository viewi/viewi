import { isBlob } from "../helpers/isBlob";
import { MethodType } from "./methodType";
import { Response } from "./response";

export function runRequest(
    callback: (response: Response) => void,
    type: MethodType,
    url: string,
    data?: any,
    headers?: { [name: string]: string | string[] }
) {
    const request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            const status = request.status;
            const contentType = request.getResponseHeader("Content-Type");
            const itsJson = contentType && contentType.indexOf('application/json') === 0;
            const raw = request.responseText;
            let content = raw;
            if (itsJson) {
                content = JSON.parse(request.responseText);
            }
            const headers = {};
            const headersString = request.getAllResponseHeaders();
            if (headersString) {
                const headersArray = headersString.trim().split(/[\r\n]+/);
                for (let i = 0; i < headersArray.length; i++) {
                    const line = headersArray[i];
                    const parts = line.split(": ");
                    const header = parts.shift();
                    if (header) {
                        const value = parts.join(": ");
                        headers[header] = value;
                    }
                };
            }
            const response = new Response(url, status, '', headers, content);
            callback(response);
        }
    }
    const isJson = data !== null && typeof data === 'object' && !isBlob(data);
    request.open(type.toUpperCase(), url, true);
    if (isJson) {
        request.setRequestHeader('Content-Type', 'application/json');
    }
    if (headers) {
        for (const h in headers) {
            if (Array.isArray(headers[h])) {
                for (let i = 0; i < headers[h].length; i++) {
                    request.setRequestHeader(h, headers[h][i]);
                }
            } else {
                request.setRequestHeader(h, <string>headers[h]);
            }
        }
    }
    data !== null ?
        request.send(isJson ? JSON.stringify(data) : data)
        : request.send();
}