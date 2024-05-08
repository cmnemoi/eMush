import sanitizeHtml from 'sanitize-html';

import { getImgUrl } from './getImgUrl';

import { AlertEnum, AlertsIcons } from '@/enums/alerts.enum';
import { CharacterEnum, characterEnum } from '@/enums/character';
import { statusPlayerEnum, StatusPlayerNameEnum } from '@/enums/status.player.enum';
import { statusItemEnum, StatusItemNameEnum } from '@/enums/status.item.enum';
import { titleEnum, TitleEnum } from '@/enums/title.enum';

export const helpers = {
    computeImageHtml(key: string): string {
        switch(key) {
        case "hp":
            return `<img src="${getImgUrl('lp.png')}" alt="hp">`;
        case "pa":
            return `<img src="${getImgUrl('pa.png')}" alt="pa">`;
        case "pm":
            return `<img src="${getImgUrl('pm.png')}" alt="pm">`;
        case "pmo":
            return `<img src="${getImgUrl('moral.png')}" alt="pmo">`;
        case "triumph":
            return `<img src="${getImgUrl('triumph.png')}" alt="pmo">`;
        case "ill":
            return `<img src="${getImgUrl('status/disease.png')}" alt="ill">`;
        case "pill":
            return `<img src="${getImgUrl('status/demoralized2.png')}" alt="pill">`;
        case "dead":
            return `<img src="${getImgUrl('dead.png')}" alt="dead">`;
        case "cat":
            return `<img src="${getImgUrl('char/body/cat.png')}" alt="cat">`;
        case "hurt":
            return `<img src="${getImgUrl('status/injury.png')}" alt="hurt">`;
        case "psy_disease":
            return `<img src="${getImgUrl('status/disorder.png')}" alt="psy_disease">`;
        case "hungry":
            return `<img src="${getImgUrl('status/hungry.png')}" alt="hungry">`;
        case "talkie":
            return `<img src="${getImgUrl('comms/talkie.png')}" alt="talkie">`;
        case "mush":
            return `<img src="${getImgUrl('status/mush.png')}" alt="mush">`;
        case "pa_cook":
            return `<img src="${getImgUrl('action_points/pa_cook.png')}" alt="pa_cook">`;
        case "hunter":
            return `<img src="${getImgUrl('alerts/hunter.png')}" alt="hunter">`;
        case "pa_shoot":
            return `<img src="${getImgUrl('action_points/pa_shoot.png')}" alt="pa_shoot">`;
        case "pa_core":
            return `<img src="${getImgUrl('action_points/pa_core.png')}" alt="pa_core">`;
        case "planet":
            return `<img src="${getImgUrl('planet.png')}" alt="planet">`;
        case "fuel":
            return `<img src="${getImgUrl('fuel.png')}" alt="fuel">`;
        case "point":
            return `<img src="${getImgUrl('point.png')}" alt="point">`;
        case "pa_pilgred":
            return `<img src="${getImgUrl('action_points/pa_pilgred.png')}" alt="pa_pilgred">`;
        case "pa_eng":
            return `<img src="${getImgUrl('action_points/pa_eng.png')}" alt="pa_eng">`;
        case "pa_garden":
            return `<img src="${getImgUrl('action_points/pa_garden.png')}" alt="pa_garden">`;
        case "pa_comp":
            return `<img src="${getImgUrl('action_points/pa_comp.png')}" alt="pa_computer">`;
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
    formattedText = formattedText.replace(/(?<!http:|https:)\/\//g, '<br>');
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
    formattedText = formattedText.replaceAll(/:pa_shoot:/g, helpers.computeImageHtml("pa_shoot"));
    formattedText = formattedText.replaceAll(/:pa_core:/g, helpers.computeImageHtml("pa_core"));
    formattedText = formattedText.replaceAll(/:planet:/g, helpers.computeImageHtml("planet"));
    formattedText = formattedText.replaceAll(/:fuel:/g, helpers.computeImageHtml("fuel"));
    formattedText = formattedText.replaceAll(/:point:/g, helpers.computeImageHtml("point"));
    formattedText = formattedText.replaceAll(/:pa_pilgred:/g, helpers.computeImageHtml("pa_pilgred"));
    formattedText = formattedText.replaceAll(/:pa_eng:/g, helpers.computeImageHtml("pa_eng"));
    formattedText = formattedText.replaceAll(/:pa_comp:/g, helpers.computeImageHtml("pa_comp"));
    formattedText = formattedText.replaceAll(/:pa_garden:/g, helpers.computeImageHtml("pa_garden"));
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
