import { resources } from "../../../app/main/resources";

export const register: { [name: string]: any } = window.ViewiApp ? window.ViewiApp[resources.name].register : {};