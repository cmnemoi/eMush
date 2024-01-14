import sanitizeHtml from 'sanitize-html';

import { AlertEnum, AlertsIcons } from '@/enums/alerts.enum';
import { CharacterEnum, characterEnum } from '@/enums/character';
import { StatusPlayerNameEnum, statusPlayerEnum } from '@/enums/status.player.enum';
import { StatusItemNameEnum, statusItemEnum } from '@/enums/status.item.enum';
import { titleEnum, TitleEnum } from '@/enums/title.enum';

export const helpers = {
    computeImageHtml(key: string): string {
        switch(key) {
        case "hp":
            return `<img src="${require("@/assets/images/lp.png")}" alt="hp">`;
        case "pa":
            return `<img src="${require("@/assets/images/pa.png")}" alt="pa">`;
        case "pm":
            return `<img src="${require("@/assets/images/pm.png")}" alt="pm">`;
        case "pmo":
            return `<img src="${require("@/assets/images/moral.png")}" alt="pmo">`;
        case "triumph":
            return `<img src="${require("@/assets/images/triumph.png")}" alt="pmo">`;
        case "ill":
            return `<img src="${require("@/assets/images/status/disease.png")}" alt="ill">`;
        case "pill":
            return `<img src="${require("@/assets/images/status/demoralized2.png")}" alt="pill">`;
        case "dead":
            return `<img src="${require("@/assets/images/dead.png")}" alt="dead">`;
        case "cat":
            return `<img src="${require("@/assets/images/char/body/cat.png")}" alt="cat">`;
        case "hurt":
            return `<img src="${require("@/assets/images/status/injury.png")}" alt="hurt">`;
        case "psy_disease":
            return `<img src="${require("@/assets/images/status/disorder.png")}" alt="psy_disease">`;
        case "hungry":
            return `<img src="${require("@/assets/images/status/hungry.png")}" alt="hungry">`;
        case "talkie":
            return `<img src="${require("@/assets/images/comms/talkie.png")}" alt="talkie">`;
        case "mush":
            return `<img src="${require("@/assets/images/status/mush.png")}" alt="mush">`;
        case "pa_cook":
            return `<img src="${require("@/assets/images/pa_cook.png")}" alt="pa_cook">`;
        case "hunter":
            return `<img src="${require("@/assets/images/alerts/hunter.png")}" alt="hunter">`;
        default:
            throw Error(`Unexpected key for replaced image: ${key}`);
        }
    },
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
    formattedText = formattedText.replaceAll(/[^:]\/\//g, '<br>');
    formattedText = formattedText.replaceAll(/:pa:/g, helpers.computeImageHtml("pa"));
    formattedText = formattedText.replaceAll(/:pm:/g, helpers.computeImageHtml("pm"));
    formattedText = formattedText.replaceAll(/:pmo:/g, helpers.computeImageHtml("pmo"));
    formattedText = formattedText.replaceAll(/:hp:/g, helpers.computeImageHtml("hp"));
    formattedText = formattedText.replaceAll(/:triumph:/g, helpers.computeImageHtml("triumph"));
    formattedText = formattedText.replaceAll(/:ill:/g, helpers.computeImageHtml("ill"));
    formattedText = formattedText.replaceAll(/:pill:/g, helpers.computeImageHtml("pill"));
    formattedText = formattedText.replaceAll(/:dead:/g, helpers.computeImageHtml("dead"));
    formattedText = formattedText.replaceAll(/:cat:/g, helpers.computeImageHtml("cat"));
    formattedText = formattedText.replaceAll(/:hurt:/g, helpers.computeImageHtml("hurt"));
    formattedText = formattedText.replaceAll(/:psy_disease:/g, helpers.computeImageHtml("psy_disease"));
    formattedText = formattedText.replaceAll(/:hungry:/g, helpers.computeImageHtml("hungry"));
    formattedText = formattedText.replaceAll(/:talkie:/g, helpers.computeImageHtml("talkie"));
    formattedText = formattedText.replaceAll(/:mush:/g, helpers.computeImageHtml("mush"));
    formattedText = formattedText.replaceAll(/:pa_cook:/g, helpers.computeImageHtml("pa_cook"));
    formattedText = formattedText.replaceAll(/:hunter:/g, helpers.computeImageHtml("hunter"));
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

    return formattedText;
}
