import { Request } from "./request";

export type IRequestHandler = {
    next(request: Request): void;
    reject(request: Request): void;
};