import { GameLocales } from "@/i18n";
import urlJoin from "url-join";

export const getDiscordLink = (): string => "https://discord.gg/eternaltwin";
export const getForumLink = (): string => urlJoin(import.meta.env.VITE_ETERNALTWIN_URL, "forum", "sections", "b5ddc792-0738-4289-9818-c2f1f029c8b1");
export const getWikiLink = (locale: string): string => {
    switch (locale) {
    case GameLocales.FR:
        return 'https://fr.emushpedia.com/';
    case GameLocales.EN:
        return 'https://en.emushpedia.com/';
    case GameLocales.ES:
        return 'https://es.emushpedia.com/';
    default:
        return 'https://fr.emushpedia.com/';
    }
};
