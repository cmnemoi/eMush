import { EmoteCharacterAliases, EmoteCharacterEnum, EmoteCharacterIcons } from "@/enums/emotes/characters.enum";
import { EmoteResourcesAliases, EmoteResourcesEnum, EmoteResourcesIcons } from "@/enums/emotes/resources.enum";
import { EmoteStatusAliases, EmoteStatusEnum, EmoteStatusIcons } from "@/enums/emotes/status.enum";
import {
    EmoteHumanSkillAliases, EmoteHumanSkillEnum, EmoteHumanSkillIcons, EmoteMushSkillAliases, EmoteMushSkillEnum, EmoteMushSkillIcons
} from "@/enums/emotes/skills.enum";
import { EmoteAstroAliases, EmoteAstroEnum, EmoteAstroIcons } from "@/enums/emotes/astro.enum";
import { EmoteEternalTwinAliases, EmoteEternalTwinEnum, EmoteEternalTwinIcons } from "@/enums/emotes/eternaltwin.enum";
import { EmoteIconAliases, EmoteIconEnum, EmoteIconIcons } from "@/enums/emotes/icons.enum";

export type FormattingType = 'bold' | 'italic' | 'bolditalic' | 'strike';
export type RichTextEditorButtonType = 'erase' | FormattingType;

export interface RichTextEditorFormattingButtonConfig {
    type: RichTextEditorButtonType;
    label: string;
    title: string;
    action: 'clearFormatting' | 'applyFormatting';
    actionParam?: string;
}

export const richTextEditorFormattingButtons: RichTextEditorFormattingButtonConfig[] = [
    {
        type: 'bold',
        label: 'game.communications.boldButtonTitle',
        title: 'game.communications.boldButtonDescription',
        action: 'applyFormatting',
        actionParam: 'bold'
    },
    {
        type: 'italic',
        label: 'game.communications.italicButtonTitle',
        title: 'game.communications.italicButtonDescription',
        action: 'applyFormatting',
        actionParam: 'italic'
    },
    {
        type: 'bolditalic',
        label: 'game.communications.boldItalicButtonTitle',
        title: 'game.communications.boldItalicButtonDescription',
        action: 'applyFormatting',
        actionParam: 'bolditalic'
    },
    {
        type: 'strike',
        label: 'game.communications.strikeButtonTitle',
        title: 'game.communications.strikeButtonDescription',
        action: 'applyFormatting',
        actionParam: 'strike'
    },
    {
        type: 'erase',
        label: 'game.communications.eraseButtonTitle',
        title: 'game.communications.eraseButtonDescription',
        action: 'clearFormatting'
    }
];

export interface RichTextEditorEmoteButtonConfig {
    icon: string,
    emoteEnum: Record<string, string>;
    aliasesEnum: Record<string, string>;
    iconEnum: {[index: string]: string};
}

export const richTextEditorEmoteButtons: RichTextEditorEmoteButtonConfig[] = [
    {
        icon: EmoteCharacterIcons[EmoteCharacterEnum.ELEESHA],
        emoteEnum: EmoteCharacterEnum,
        aliasesEnum: EmoteCharacterAliases,
        iconEnum: EmoteCharacterIcons
    },
    {
        icon: EmoteResourcesIcons[EmoteResourcesEnum.AP],
        emoteEnum: EmoteResourcesEnum,
        aliasesEnum: EmoteResourcesAliases,
        iconEnum: EmoteResourcesIcons
    },
    {
        icon: EmoteIconIcons[EmoteIconEnum.BROKEN],
        emoteEnum: EmoteIconEnum,
        aliasesEnum: EmoteIconAliases,
        iconEnum: EmoteIconIcons
    },
    {
        icon: EmoteStatusIcons[EmoteStatusEnum.STINKY],
        emoteEnum: EmoteStatusEnum,
        aliasesEnum: EmoteStatusAliases,
        iconEnum: EmoteStatusIcons
    },
    {
        icon: EmoteHumanSkillIcons[EmoteHumanSkillEnum.GENIUS],
        emoteEnum: EmoteHumanSkillEnum,
        aliasesEnum: EmoteHumanSkillAliases,
        iconEnum: EmoteHumanSkillIcons
    },
    {
        icon: EmoteMushSkillIcons[EmoteMushSkillEnum.ANONYMOUS],
        emoteEnum: EmoteMushSkillEnum,
        aliasesEnum: EmoteMushSkillAliases,
        iconEnum: EmoteMushSkillIcons
    },
    {
        icon: EmoteAstroIcons[EmoteAstroEnum.CRISTALITE],
        emoteEnum: EmoteAstroEnum,
        aliasesEnum: EmoteAstroAliases,
        iconEnum: EmoteAstroIcons
    },
    {
        icon: EmoteEternalTwinIcons[EmoteEternalTwinEnum.ETERNALTWIN],
        emoteEnum: EmoteEternalTwinEnum,
        aliasesEnum: EmoteEternalTwinAliases,
        iconEnum: EmoteEternalTwinIcons
    }
];
