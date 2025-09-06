import { LocalStorageServiceInterface } from "./local.storage.service";

export class FakeLocalStorageService implements LocalStorageServiceInterface {
    private storage: Record<string, string> = {};

    getItemAsBoolean(key: string): boolean {
        return this.storage[key] === 'true';
    }

    getItemAsBooleanOrNull(key: string): boolean | null {
        const item = this.storage[key];
        if (!item) {
            return null;
        }
        return item === 'true';
    }

    setItemAsBoolean(key: string, value: boolean): void {
        this.storage[key] = JSON.stringify(value);
    }

    setItem(key: string, value: string): void {
        this.storage[key] = value;
    }

    removeItem(key: string): void {
        delete this.storage[key];
    }

    getItemAsArray(key: string): string[] {
        const item = this.storage[key];
        if (!item) {
            return [];
        }

        return JSON.parse(item);
    }

    saveItemAsArray(key: string, value: string[]): void {
        this.storage[key] = JSON.stringify(value);
    }
}
