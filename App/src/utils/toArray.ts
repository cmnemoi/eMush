export function toArray(data: Array<any> | object): any[] {
    return Array.isArray(data) ? data : Object.values(data);
}
