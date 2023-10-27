import { componentsMeta } from "../component/componentsMeta";

class Process {
    browser: true = true;
    server: false = false;

    getConfig() {
        return componentsMeta.config;
    }
}

export { Process }