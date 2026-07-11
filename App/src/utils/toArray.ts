export function toArray<T>(data: Array<T> | Record<string, T>): T[] {
    return Array.isArray(data) ? data : Object.values(data);
}
