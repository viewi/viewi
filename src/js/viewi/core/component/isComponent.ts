import { componentsMeta } from "./componentsMeta";

export function isComponent(name: string) {
    return (name in componentsMeta.list);
}