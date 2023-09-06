
export type DirectiveType = 'if' | 'else-if' | 'else' | 'foreach';

export type Directive = {
    type: DirectiveType
}

export type ConditionalDirective = {
    values: boolean[],
    index: number
}

export type DirectiveMap = {
    map: { [key: number]: boolean },
    storage: { [key: string]: any }
}

export enum DirectiveStorageType {
    Condition = 'conditions',
    Foreach = 'foreach'
}