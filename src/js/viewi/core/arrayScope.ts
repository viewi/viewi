import { TextAnchor } from "./anchor";

export type ArrayScope = { [key: string | number]: { key: string | number, value: any, begin: TextAnchor, end: TextAnchor } };

export enum ForeachAnchorEnum {
    BeginAnchor = 'b',
    EndAnchor = 'e'
};