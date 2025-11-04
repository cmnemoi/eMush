import sanitizeHtml from 'sanitize-html';

import { AlertEnum, AlertsIcons } from '@/enums/alerts.enum';
import { CharacterEnum, characterEnum } from '@/enums/character';
import { statusPlayerEnum, StatusPlayerNameEnum } from '@/enums/status.player.enum';
import { statusItemEnum, StatusItemNameEnum } from '@/enums/status.item.enum';
import { titleEnum, TitleEnum } from '@/enums/title.enum';
import { EmoteEnum, EmoteIcons } from '@/enums/emotes.enum';
import { UiIconEnum, UiIconIcons } from '@/enums/ui_icon.enum';
import { SkillPointEnum, skillPointEnum } from '@/enums/skill.point.enum';

export const helpers = {
    computeCharacterImageHtmlByKey(key: string): string {
        if (!characterEnum[key]) {
            throw Error(`Unexpected key for replaced image: ${key}`);
        }

        return `<img src="${characterEnum[key].head}" alt="${key}">`;
    },
    computeAlertImageHtmlByKey(key: string): string {
        if (!AlertsIcons[key]) {
            throw Error(`Unexpected key for replaced image: ${key}`);
        }

        return `<img src="${AlertsIcons[key]}" alt="${key}">`;
    },
    computeItemStatusImageHtmlByKey(key: string): string {
        if (!statusItemEnum[key]) {
            throw Error(`Unexpected key for replaced image: ${key}`);
        }

        return `<img src="${statusItemEnum[key].icon}" alt="${key}">`;
    },
    computePlayerStatusImageHtmlByKey(key: string): string {
        if (!statusPlayerEnum[key]) {
            throw Error(`Unexpected key for replaced image: ${key}`);
        }

        return `<img src="${statusPlayerEnum[key].icon}" alt="${key}">`;
    },
    computeTitleImageHtmlByKey(key: string): string {
        if (!titleEnum[key]) {
            throw Error(`Unexpected key for replaced image: ${key}`);
        }

        return `<img src="${titleEnum[key].image}" alt="${key}">`;
    },
    computeEmoteHtmlByKey(key: string): string {
        if (!EmoteIcons[key]) {
            throw Error(`Unexpected key for replaced image: ${key}`);
        }

        return `<img src="${EmoteIcons[key]}" alt="${key}">`;
    },
    computeUiIconHtmlByKey(key: string): string {
        if (!UiIconIcons[key]) {
            throw Error(`Unexpected key for replaced image: ${key}`);
        }

        return `<img src="${UiIconIcons[key]}" alt="${key}">`;
    },
    computeSkillPointIconHtmlByKey(key: string): string {
        if (!skillPointEnum[key]) {
            throw Error(`Unexpected key for replaced image: ${key}`);
        }

        return `<img src="${skillPointEnum[key].icon}" alt="${key}">`;
    }
};

export function regexEscape(text: string): string {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

export function formatText(text: string|null): string {
    if (text === null) {
        return "";
    }

    let formattedText = sanitizeHtml(text, {
        allowedTags: [ 'strong', 'em', 'a', 'br' ],
        allowedAttributes: {
            'a': [ 'href' ]
        }
    });

    // Handle both markdown-style links (e.g [Mushpedia](https://mushpedia.com))
    // and direct links (e.g. https://emush.eternaltwin.org/news) to known hosts.
    function markdownSubstitution(substring: string, p1: string, p2: string, p3: string): string {
        return !p1 ? `<a href='${p3}' title='${p3}' target='_blank' rel='noopener noreferrer'>${p3}</a>` : `<a href='${p2}' title='${p2}' target='_blank' rel='noopener noreferrer'>${p1}</a>`;
    }
    const knownHosts = [
        "localhost",
        "emush.eternaltwin.org",
        "staging.emush.eternaltwin.org",
        "emushpedia.miraheze.org",
        "www.mushpedia.com"
    ];
    const knowHostsRegex = "(?:(?:" + knownHosts.map(regexEscape).join(")|(?:") + "))";
    const markdownLinkRegex = new RegExp(
        String.raw`\[([^\]]+)\]\((https?:\/\/${knowHostsRegex}[^\s)\]'"<]*)\)|(https?:\/\/${knowHostsRegex}[^;:!?.\s)\]'"<]*)`,
        "gi"
    );
    formattedText = formattedText.replaceAll(markdownLinkRegex, markdownSubstitution);


    // Text formatting
    formattedText = formattedText
        .replaceAll(/^(\/neron )/ig, '<strong class="neron">/neron </strong>')
        .replaceAll(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>')
        .replaceAll(/(?<!\*)\*\*(?!\*)(.+?)(?<!\*)\*\*(?!\*)/g, '<strong>$1</strong>')
        .replaceAll(/\*\*\*(?!\*)(.+?)(?<!\*)\*\*\*/g, '<strong><em>$1</em></strong>')
        .replaceAll(/~~([^~]+?)~~/g, '<s>$1</s>')
        .replaceAll(/(?<!https?:)\/\//g, "<br>");

    // Emotes
    Object.values(CharacterEnum).forEach((character: string) => {
        formattedText = formattedText.replaceAll(new RegExp(`:${character}:`, 'g'), helpers.computeCharacterImageHtmlByKey(character));
    });
    Object.values(AlertEnum).forEach((alert: string) => {
        formattedText = formattedText.replaceAll(new RegExp(`:${alert}:`, 'g'), helpers.computeAlertImageHtmlByKey(alert));
    });
    Object.values(StatusItemNameEnum).forEach((statusItem: any) => {
        formattedText = formattedText.replaceAll(new RegExp(`:${statusItem}:`, 'g'), helpers.computeItemStatusImageHtmlByKey(statusItem));
    });
    Object.values(StatusPlayerNameEnum).forEach((statusPlayer: string) => {
        formattedText = formattedText.replaceAll(new RegExp(`:${statusPlayer}:`, 'g'), helpers.computePlayerStatusImageHtmlByKey(statusPlayer));
    });
    Object.values(TitleEnum).forEach((title: string) => {
        formattedText = formattedText.replaceAll(new RegExp(`:${title}:`, 'g'), helpers.computeTitleImageHtmlByKey(title));
    });
    Object.values(EmoteEnum).forEach((emote: string) => {
        formattedText = formattedText.replaceAll(new RegExp(`:${emote}:`, 'g'), helpers.computeEmoteHtmlByKey(emote));
    });
    Object.values(UiIconEnum).forEach((uiIcon: string) => {
        formattedText = formattedText.replaceAll(new RegExp(`:${uiIcon}:`, 'g'), helpers.computeUiIconHtmlByKey(uiIcon));
    });
    Object.values(SkillPointEnum).forEach((skillPoint: string) => {
        formattedText = formattedText.replaceAll(new RegExp(`:${skillPoint}:`, 'g'), helpers.computeSkillPointIconHtmlByKey(skillPoint));
    });

    return formattedText;
}

/**
 * Like `formatText()` but actually keep the Markdown syntax visible.
 */
export function formatSyntax(text: string | null): string {
    if (text === null) return "";

    let formattedText = sanitizeHtml(text, {
        allowedTags: [ 'strong', 'em', 'a', 's', 'br' ],
        allowedAttributes: { 'a': [ 'href', 'title' ] }
    });

    // Handle both markdown-style links (e.g [Mushpedia](https://mushpedia.com))
    // and direct links (e.g. https://emush.eternaltwin.org/news) to known hosts.
    const knownHosts = [
        "localhost",
        "emush.eternaltwin.org",
        "staging.emush.eternaltwin.org",
        "emushpedia.miraheze.org",
        "www.mushpedia.com"
    ];
    const knowHostsRegex = "(?:(?:" + knownHosts.map(regexEscape).join(")|(?:") + "))";
    const markdownLinkRegex = new RegExp(
        String.raw`\[([^\]]+)\]\((https?:\/\/${knowHostsRegex}[^\s)\]'"<]*)\)|(https?:\/\/${knowHostsRegex}[^,;:!?.\s)\]'"<]*)`, "gi"
    );
    formattedText = formattedText.replaceAll(
        markdownLinkRegex,
        (substring, p1, p2, p3) => {
            if (p1 && p2) {
                // Markdown link
                const safeUrl = sanitizeHtml(p2, { allowedTags: [], allowedAttributes: {} });
                const safeLabel = sanitizeHtml(p1, { allowedTags: [], allowedAttributes: {} });
                return `<a href='${safeUrl}' title='${safeUrl}' target='_blank' rel='noopener noreferrer'>[${safeLabel}](${safeUrl})</a>`;
            } else if (p3) {
                // Raw link
                const safeUrl = sanitizeHtml(p3, { allowedTags: [], allowedAttributes: {} });
                return `<a href='${safeUrl}' title='${safeUrl}' target='_blank' rel='noopener noreferrer'>${safeUrl}</a>`;
            }
            return substring;
        }
    );

    // Text formatting
    formattedText = formattedText
        .replaceAll(/^(\/neron )/ig, '<strong class="neron">/neron </strong>')
        .replaceAll(/(?<!\*)(\*(?!\*).+?(?<!\*)\*)(?!\*)/g, '<em>$1</em>')
        .replaceAll(/(?<!\*)(\*\*(?!\*).+?(?<!\*)\*\*)(?!\*)/g, '<strong>$1</strong>')
        .replaceAll(/(\*\*\*(?!\*).+?(?<!\*)\*\*\*)/g, '<strong><em>$1</em></strong>')
        .replaceAll(/(~~[^~]+?~~)/g, '<s>$1</s>');

    return formattedText;
}
