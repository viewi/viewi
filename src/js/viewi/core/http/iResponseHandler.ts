import { Response } from "./response";

export type IResponseHandler = {
    next(response: Response): void;
    reject(response: Response): void;
};