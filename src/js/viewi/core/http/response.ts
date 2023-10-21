export type Response = {
    status: number,
    headers: { [name: string]: string },
    raw: string,
    data: any,
    error?: any
}