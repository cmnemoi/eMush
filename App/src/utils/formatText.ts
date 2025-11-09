import sanitizeHtml from 'sanitize-html';

import { EmoteCharacterAliases, EmoteCharacterIcons } from "@/enums/emotes/characters.enum";
import { EmoteResourcesAliases, EmoteResourcesIcons } from "@/enums/emotes/resources.enum";
import { EmoteIconAliases, EmoteIconIcons } from "@/enums/emotes/icons.enum";
import {
    EmoteHumanSkillAliases,
    EmoteHumanSkillIcons,
    EmoteMushSkillAliases,
    EmoteMushSkillIcons
} from "@/enums/emotes/skills.enum";
import { EmoteAstroAliases, EmoteAstroIcons } from "@/enums/emotes/astro.enum";
import { EmoteEternalTwinAliases, EmoteEternalTwinIcons } from "@/enums/emotes/eternaltwin.enum";
import { EmoteStatusAliases, EmoteStatusIcons } from "@/enums/emotes/status.enum";

const emoteAliasesEnums = {
    ...EmoteCharacterAliases,
    ...EmoteResourcesAliases,
    ...EmoteIconAliases,
    ...EmoteStatusAliases,
    ...EmoteHumanSkillAliases,
    ...EmoteMushSkillAliases,
    ...EmoteAstroAliases,
    ...EmoteEternalTwinAliases
};

const emoteIconEnums = {
    ...EmoteCharacterIcons,
    ...EmoteResourcesIcons,
    ...EmoteIconIcons,
    ...EmoteStatusIcons,
    ...EmoteHumanSkillIcons,
    ...EmoteMushSkillIcons,
    ...EmoteAstroIcons,
    ...EmoteEternalTwinIcons
};

function markdownLinkSubstitution(_: string, p1: string, p2: string, p3: string): string {
    if (!p1) {
        // Direct link
        return `<a href='${p3}' title='${p3}' target='_blank' rel='noopener noreferrer'>${p3}</a>`;
    }
    // Markdown-style link
    return `<a href='${p2}' title='${p2}' target='_blank' rel='noopener noreferrer'>${p1}</a>`;
}


function emoteSubstitution(substring: string, p1: string): string {
    const key = p1 in emoteAliasesEnums ? emoteAliasesEnums[p1] : p1;
    if (key in emoteIconEnums) {
        return `<img src='${emoteIconEnums[key]}' alt='${substring}' style="max-height: 16px;">`;
    }
    return substring;
}

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
    const knownHosts = [
        "localhost",
        "emush.eternaltwin.org",
        "staging.emush.eternaltwin.org",
        "emushpedia.miraheze.org",
        "www.mushpedia.com"
    ];
    const knowHostsRegex = "(?:(?:" + knownHosts.map(regexEscape).join(")|(?:") + "))";
    const markdownLinkRegex = new RegExp(
        String.raw`\[([^\]]+)\]\((https?:\/\/${knowHostsRegex}[^\n\s)\]'"]*)\)|(https?:\/\/${knowHostsRegex}[^;:<>!?.\s\n)\]\['"]*)`,
        "gim"
    );

    formattedText = formattedText.replaceAll(markdownLinkRegex, markdownLinkSubstitution);
    formattedText = formattedText.replace(/:([a-zA-Z0-9_]+):/g, emoteSubstitution);
    formattedText = formattedText.replaceAll(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    formattedText = formattedText.replaceAll(/\*(.*?)\*/g, '<em>$1</em>');
    formattedText = formattedText.replaceAll(/~~(.*?)~~/g, '<s>$1</s>');
    formattedText = formattedText.replace(/(?<!http:|https:)\/\//g, '<br>');

    return formattedText;
}
