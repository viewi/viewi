import { IRequestHandler } from "./iRequestHandler";
import { IResponseHandler } from "./iResponseHandler";
import { Request } from "./request";
import { Response } from "./response";

export type IHttpInterceptor = {
    request(request: Request, handler: IRequestHandler): void;
    response(response: Response, handler: IResponseHandler): void;
};