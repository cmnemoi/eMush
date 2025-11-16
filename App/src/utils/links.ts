import { GameLocales } from "@/i18n";
import urlJoin from "url-join";

export const getDiscordLink = (): string => "https://discord.gg/eternaltwin-693082011484684348";
export const getForumLink = (): string => urlJoin(import.meta.env.VITE_ETERNALTWIN_URL, "forum", "sections", "b5ddc792-0738-4289-9818-c2f1f029c8b1");
export const getWikiLink = (locale: string): string => {
    switch (locale) {
    case GameLocales.FR:
        return 'https://emushpedia.miraheze.org/';
    case GameLocales.EN:
        return 'http://www.mushpedia.com/';
    case GameLocales.ES:
        return 'http://www.mushpedia.com/';
    default:
        return 'http://www.mushpedia.com/';
    }
};
