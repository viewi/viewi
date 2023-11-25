import { components } from "../../../app/main/components";
import { register } from "./register";

type Constructor = new (...args: any[]) => any;

export const factoryContainer: { [name: string]: <T>() => T } = {};

export function factory<T extends Constructor>(name: string, implementation: T, factory: () => InstanceType<T>) {
    register[name] = implementation;
    components[name] = implementation;
    factoryContainer[name] = factory;
};