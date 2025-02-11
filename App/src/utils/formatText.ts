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
    formattedText = formattedText.replaceAll(/\*\*(.[^*]*)\*\*/g, '<strong>$1</strong>');
    formattedText = formattedText.replaceAll(/\*(.[^*]*)\*/g, '<em>$1</em>');
    formattedText = formattedText.replace(/(?<!http:|https:)\/\//g, '<br>');
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

    // "Markdown style" clickable links i.e. [text of the link](https://google.com)
    const markdownLinkRegex = /\[([^\]]+)]\(([^)]+)\)/g;
    formattedText = formattedText.replaceAll(markdownLinkRegex,'<a href=\'$2\' title=\'$2\'>$1</a>');

    // All links from eMush should be clickable
    const eMushLinkRegex = /^(https:\/\/)?emush\.eternaltwin\.org\/[^\s)"']*/g;
    formattedText = formattedText.replaceAll(eMushLinkRegex, '<a href=\'$&\'>$&</a>');

    return formattedText;
}
