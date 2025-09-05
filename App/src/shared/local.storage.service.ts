export interface LocalStorageServiceInterface {
    getItemAsBoolean(key: string): boolean;
    setItemAsBoolean(key: string, value: boolean): void;
    removeItem(key: string): void;
    getItemAsArray(key: string): string[];
    saveItemAsArray(key: string, value: string[]): void;

}

export class LocalStorageService implements LocalStorageServiceInterface {
    getItemAsBoolean(key: string): boolean {
        const item = localStorage.getItem(key);
        if (!item) {
            return false;
        }
        return item === 'true';
    }

    setItemAsBoolean(key: string, value: boolean): void {
        localStorage.setItem(key, JSON.stringify(value));
    }

    removeItem(key: string): void {
        localStorage.removeItem(key);
    }

    getItemAsArray(key: string): string[] {
        const item = localStorage.getItem(key);
        if (!item) {
            return [];
        }
        return JSON.parse(item);
    }

    saveItemAsArray(key: string, value: string[]): void {
        localStorage.setItem(key, JSON.stringify(value));
    }
}
