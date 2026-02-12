import { EmoteCharacterAliases, EmoteCharacterEnum, EmoteCharacterIcons } from "@/enums/emotes/characters.enum";
import { EmoteResourcesAliases, EmoteResourcesEnum, EmoteResourcesIcons } from "@/enums/emotes/resources.enum";
import { EmoteStatusAliases, EmoteStatusEnum, EmoteStatusIcons } from "@/enums/emotes/status.enum";
import {
    EmoteHumanSkillAliases,
    EmoteHumanSkillEnum,
    EmoteHumanSkillIcons,
    EmoteMushSkillAliases,
    EmoteMushSkillEnum,
    EmoteMushSkillIcons
} from "@/enums/emotes/skills.enum";
import { EmoteAstroAliases, EmoteAstroEnum, EmoteAstroIcons } from "@/enums/emotes/astro.enum";
import { EmoteEternalTwinAliases, EmoteEternalTwinEnum, EmoteEternalTwinIcons } from "@/enums/emotes/eternaltwin.enum";
import { EmoteIconAliases, EmoteIconEnum, EmoteIconIcons } from "@/enums/emotes/icons.enum";
import { EmoteMuxxuAliases, EmoteMuxxuEnum, EmoteMuxxuIcons } from "@/enums/emotes/muxxu.enum";
import { EmoteTwinoidAliases, EmoteTwinoidEnum, EmoteTwinoidIcons } from "@/enums/emotes/twinoid.enum";

export interface EmoteTabConfig {
    icon: string,
    tooltip: string,
    emoteEnum: Record<string, string>;
    aliasesEnum: Record<string, string>;
    iconEnum: { [index: string]: Record<string, string> };
}

export const EmoteTabs: EmoteTabConfig[] = [
    {
        icon: EmoteCharacterIcons[EmoteCharacterEnum.ELEESHA].img,
        tooltip: 'game.communications.emoteCharactersTab',
        emoteEnum: EmoteCharacterEnum,
        aliasesEnum: EmoteCharacterAliases,
        iconEnum: EmoteCharacterIcons
    },
    {
        icon: EmoteResourcesIcons[EmoteResourcesEnum.AP].img,
        tooltip: 'game.communications.emoteResourcesTab',
        emoteEnum: EmoteResourcesEnum,
        aliasesEnum: EmoteResourcesAliases,
        iconEnum: EmoteResourcesIcons
    },
    {
        icon: EmoteIconIcons[EmoteIconEnum.BROKEN].img,
        tooltip: 'game.communications.emoteIconsTab',
        emoteEnum: EmoteIconEnum,
        aliasesEnum: EmoteIconAliases,
        iconEnum: EmoteIconIcons
    },
    {
        icon: EmoteStatusIcons[EmoteStatusEnum.STINKY].img,
        tooltip: 'game.communications.emoteStatusesTab',
        emoteEnum: EmoteStatusEnum,
        aliasesEnum: EmoteStatusAliases,
        iconEnum: EmoteStatusIcons
    },
    {
        icon: EmoteHumanSkillIcons[EmoteHumanSkillEnum.GENIUS].img,
        tooltip: 'game.communications.emoteHumanSkillsTab',
        emoteEnum: EmoteHumanSkillEnum,
        aliasesEnum: EmoteHumanSkillAliases,
        iconEnum: EmoteHumanSkillIcons
    },
    {
        icon: EmoteMushSkillIcons[EmoteMushSkillEnum.ANONYMOUS].img,
        tooltip: 'game.communications.emoteMushSkillsTab',
        emoteEnum: EmoteMushSkillEnum,
        aliasesEnum: EmoteMushSkillAliases,
        iconEnum: EmoteMushSkillIcons
    },
    {
        icon: EmoteAstroIcons[EmoteAstroEnum.CRISTALITE].img,
        tooltip: 'game.communications.emoteAstroTab',
        emoteEnum: EmoteAstroEnum,
        aliasesEnum: EmoteAstroAliases,
        iconEnum: EmoteAstroIcons
    },
    {
        icon: EmoteTwinoidIcons[EmoteTwinoidEnum.SMILE].img,
        tooltip: 'game.communications.emoteTwinoidTab',
        emoteEnum: EmoteTwinoidEnum,
        aliasesEnum: EmoteTwinoidAliases,
        iconEnum: EmoteTwinoidIcons
    },
    {
        icon: EmoteMuxxuIcons[EmoteMuxxuEnum.SMILE].img,
        tooltip: 'game.communications.emoteMuxxuTab',
        emoteEnum: EmoteMuxxuEnum,
        aliasesEnum: EmoteMuxxuAliases,
        iconEnum: EmoteMuxxuIcons
    },
    {
        icon: EmoteEternalTwinIcons[EmoteEternalTwinEnum.ETERNALTWIN].img,
        tooltip: 'game.communications.emoteEternalTwinTab',
        emoteEnum: EmoteEternalTwinEnum,
        aliasesEnum: EmoteEternalTwinAliases,
        iconEnum: EmoteEternalTwinIcons
    }
];
