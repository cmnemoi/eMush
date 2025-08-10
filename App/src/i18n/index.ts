import { getImgUrl } from '../utils/getImgUrl';

import en from './locales/en.json';
import fr from './locales/fr.json';
import es from './locales/es.json';

export enum GameLocales {
	EN = 'en',
	FR = 'fr',
    ES = 'es',
}

export interface LangInfos {
    caption: string,
    icon: string
};

export const gameLocales : {[index: string]: LangInfos}  = {
    [GameLocales.EN]: {
        'caption': 'English',
        'icon': getImgUrl('lang_en.png')
    },
    [GameLocales.FR]: {
        'caption': 'Français',
        'icon': getImgUrl('lang_fr.png')
    },
    [GameLocales.ES]: {
        'caption': 'Español',
        'icon': getImgUrl('lang_es.png')
    }
};


export const messages = {
    [GameLocales.EN]: en,
    [GameLocales.FR]: fr,
    [GameLocales.ES]: es
};
export const defaultLocale = GameLocales.FR;
