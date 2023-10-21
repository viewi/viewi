export function isBlob(data: any) {
    if ('Blob' in window && data instanceof Blob) {
        return true;
    }
    return false;
}