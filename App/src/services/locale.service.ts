const LOCALE_KEY = 'locale';

const LocaleService = {
    getLocale(): string | null {
        return localStorage.getItem(LOCALE_KEY);
    },

    saveLocale(locale: string): void {
        localStorage.setItem(LOCALE_KEY, locale);
    },

    removeLocale(): void {
        localStorage.removeItem(LOCALE_KEY);
    }
};

export { LocaleService };
