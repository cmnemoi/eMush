import { i18n } from '@/main';

export const translate = (key: string, params: Record<string, unknown> = {}): string => {
    return i18n.global.t(key, params) as string;
};
