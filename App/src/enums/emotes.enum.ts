import { EmoteAstroAliases, EmoteAstroIcons } from "@/enums/emotes/astro.enum";
import { EmoteCharacterAliases, EmoteCharacterIcons } from "@/enums/emotes/characters.enum";
import { EmoteEternalTwinAliases, EmoteEternalTwinIcons } from "@/enums/emotes/eternaltwin.enum";
import {
    EmoteHumanSkillAliases,
    EmoteHumanSkillIcons,
    EmoteMushSkillAliases,
    EmoteMushSkillIcons
} from "@/enums/emotes/skills.enum";
import { EmoteIconAliases, EmoteIconIcons } from "@/enums/emotes/icons.enum";
import { EmoteMuxxuAliases, EmoteMuxxuIcons } from "@/enums/emotes/muxxu.enum";
import { EmoteResourcesAliases, EmoteResourcesIcons } from "@/enums/emotes/resources.enum";
import { EmoteStatusAliases, EmoteStatusIcons } from "@/enums/emotes/status.enum";
import { EmoteTwinoidAliases, EmoteTwinoidIcons } from "@/enums/emotes/twinoid.enum";


export const emoteAliasesEnums = {
    ...EmoteAstroAliases,
    ...EmoteCharacterAliases,
    ...EmoteEternalTwinAliases,
    ...EmoteHumanSkillAliases,
    ...EmoteIconAliases,
    ...EmoteMushSkillAliases,
    ...EmoteMuxxuAliases,
    ...EmoteResourcesAliases,
    ...EmoteStatusAliases,
    ...EmoteTwinoidAliases
};

export const emoteIconEnums = {
    ...EmoteAstroIcons,
    ...EmoteCharacterIcons,
    ...EmoteEternalTwinIcons,
    ...EmoteHumanSkillIcons,
    ...EmoteIconIcons,
    ...EmoteMushSkillIcons,
    ...EmoteMuxxuIcons,
    ...EmoteResourcesIcons,
    ...EmoteStatusIcons,
    ...EmoteTwinoidIcons
};
