import sanitizeHtml from 'sanitize-html';
import { emoteAliasesEnums, emoteIconEnums } from "@/enums/emotes.enum";

/**
 * Return a RegExp matching a URL.
 *
 * Handle both markdown-style links (e.g [Mushpedia](https://mushpedia.com)) and
 * direct links (e.g. https://emush.eternaltwin.org/news) to known hosts.
 *
 * Handle parenthesis pairs in the path component of the URL.
 */
function getMarkdownLinkRegex() {
    const knownHosts = [
        "localhost",
        "emush.eternaltwin.org",
        "staging.emush.eternaltwin.org",
        "emushpedia.miraheze.org",
        "www.mushpedia.com",
        "gitlab.com",
        "eternaltwin.org"
    ];

    // Regex components
    const knownHostsRegex = "(?:(?:" + knownHosts.map(regexEscape).join(")|(?:") + "))";
    const textUrl = String.raw`\[([^\[\]]+)\]`;
    const path = String.raw`[^;:<>!\]\[?.\s\)\(]*`;
    const allowParenthesisPair = String.raw`(?:(?:${path})|(?:\(${path}\)))*`;
    const url = String.raw`(https?:\/\/${knownHostsRegex}${allowParenthesisPair})`;
    const markdownUrl = String.raw`${textUrl}\(${url}\)`;

    return new RegExp(String.raw`(?:${markdownUrl})|(?:${url})`, "gim");
}


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
        return `<img src='${emoteIconEnums[key].img}' alt='${substring}' style="max-height: ${emoteIconEnums[key].max_height};">`;
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

    const markdownLinkRegex = getMarkdownLinkRegex();
    formattedText = formattedText.replace(/(?<!http:|https:)\/\//g, '<br>');
    formattedText = formattedText.replace(/ {2,}/g, ' ');
    formattedText = formattedText.replace(/<br> /g, '<br>');
    formattedText = formattedText.replaceAll(markdownLinkRegex, markdownLinkSubstitution);
    formattedText = formattedText.replace(/:([a-zA-Z0-9_]+):/g, emoteSubstitution);
    formattedText = formattedText.replaceAll(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    formattedText = formattedText.replaceAll(/\*(.*?)\*/g, '<em>$1</em>');
    formattedText = formattedText.replaceAll(/~~(.*?)~~/g, '<s>$1</s>');

    return formattedText;
}
