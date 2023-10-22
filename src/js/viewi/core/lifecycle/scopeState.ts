type ScopeState = { http: { [key: string]: any }, state: { [component: string]: { [prop: string]: any } } };

export function getScopeState(): ScopeState {
    const scopedResponseData: undefined | ScopeState = (<any>window).viewiScopeState;
    return scopedResponseData ?? { http: {}, state: {} };
}