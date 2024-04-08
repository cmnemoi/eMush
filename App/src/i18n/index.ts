import { getImgUrl } from '../utils/getImgUrl';

import en from './locales/en.json';
import fr from './locales/fr.json';
import de from './locales/de.json';
import es from './locales/es.json';

export enum GameLocales {
	EN = 'en',
	FR = 'fr',
    // TODO: not supported yet
    // DE = 'de',
    // ES = 'es',
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
    }
    // TODO: not supported yet
    // [GameLocales.ES]: {
    //     'caption': 'Spanish',
    //     'icon': getImgUrl('lang_es.png'),
    // },
    // [GameLocales.DE]: {
    //     'caption': 'German',
    //     'icon': getImgUrl('lang_de.png'),
    // }
};


export const messages = {
    [GameLocales.EN]: en,
    [GameLocales.FR]: fr
    // TODO: not supported yet
    // [GameLocales.DE]: de,
    // [GameLocales.ES]: es,
};
export const defaultLocale = GameLocales.FR;
