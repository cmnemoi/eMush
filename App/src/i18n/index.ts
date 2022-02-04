import en from './locales/en.json';
import fr from './locales/fr.json';
import de from './locales/de.json';
import es from './locales/es.json';

export enum GameLocales {
	EN = 'en-EN',
	FR = 'fr-FR',
    DE = 'de-DE',
    ES = 'es-ES',
}

export interface LangInfos {
    caption: string,
    icon: string
};

export const gameLocales : {[index: string]: LangInfos}  = {
    [GameLocales.EN]: {
        'caption': 'English',
        'icon': require('@/assets/images/lang_en.png')
    },
    [GameLocales.FR]: {
        'caption': 'Français',
        'icon': require('@/assets/images/lang_fr.png')
    },
    [GameLocales.ES]: {
        'caption': 'Spanish',
        'icon': require('@/assets/images/lang_es.png')
    },
    [GameLocales.DE]: {
        'caption': 'German',
        'icon': require('@/assets/images/lang_de.png')
    }
};


export const messages = {
    [GameLocales.EN]: en,
    [GameLocales.DE]: de,
    [GameLocales.ES]: es,
    [GameLocales.FR]: fr
};
export const defaultLocale = GameLocales.FR;
