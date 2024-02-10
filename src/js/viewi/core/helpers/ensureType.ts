export const ensureTypeOptions: {
    enable: boolean
} = {
    enable: false
}

export function ensureType(targetType: Function, obj: object) {
    if (ensureTypeOptions.enable && Object.getPrototypeOf(obj) !== targetType.prototype) {
        ensureTypeOptions.enable = false;
        Object.setPrototypeOf(obj, targetType.prototype);
    }
}