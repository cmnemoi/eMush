import { getImgUrl } from "@/utils/getImgUrl";

export enum SkillPointEnum {
    COMPUTER = "computer",
    COOK = "cook",
    CORE = "core",
    ENGINEER = "engineer",
    GARDEN = "garden",
    HEAL = "heal",
    PILGRED = "pilgred",
    SHOOT = "shoot",
}

export const skillPointEnum: {[index: string]: any} = {
    [SkillPointEnum.COMPUTER]: {
        'icon': getImgUrl('action_points/pa_comp.png')
    },
    [SkillPointEnum.COOK]: {
        'icon': getImgUrl('action_points/pa_cook.png')
    },
    [SkillPointEnum.CORE]: {
        'icon': getImgUrl('action_points/pa_core.png')
    },
    [SkillPointEnum.ENGINEER]: {
        'icon': getImgUrl('action_points/pa_eng.png')
    },
    [SkillPointEnum.GARDEN]: {
        'icon': getImgUrl('action_points/pa_garden.png')
    },
    [SkillPointEnum.HEAL]: {
        'icon': getImgUrl('action_points/pa_heal.png')
    },
    [SkillPointEnum.PILGRED]: {
        'icon': getImgUrl('action_points/pa_pilgred.png')
    },
    [SkillPointEnum.SHOOT]: {
        'icon': getImgUrl('action_points/pa_shoot.png')
    }
};
