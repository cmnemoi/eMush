import { getImgUrl } from "@/utils/getImgUrl";

export enum SpecialistPointEnum {
    COMPUTER = "computer",
    COOK = "cook",
    CORE = "core",
    ENGINEER = "engineer",
    GARDEN = "garden",
    HEAL = "heal",
    PILGRED = "pilgred",
    SHOOT = "shoot",
}

export const specialistPointEnum: {[index: string]: any} = {
    [SpecialistPointEnum.COMPUTER]: {
        'icon': getImgUrl('action_points/pa_comp.png')
    },
    [SpecialistPointEnum.COOK]: {
        'icon': getImgUrl('action_points/pa_cook.png')
    },
    [SpecialistPointEnum.CORE]: {
        'icon': getImgUrl('action_points/pa_core.png')
    },
    [SpecialistPointEnum.ENGINEER]: {
        'icon': getImgUrl('action_points/pa_eng.png')
    },
    [SpecialistPointEnum.GARDEN]: {
        'icon': getImgUrl('action_points/pa_garden.png')
    },
    [SpecialistPointEnum.HEAL]: {
        'icon': getImgUrl('action_points/pa_heal.png')
    },
    [SpecialistPointEnum.PILGRED]: {
        'icon': getImgUrl('action_points/pa_pilgred.png')
    },
    [SpecialistPointEnum.SHOOT]: {
        'icon': getImgUrl('action_points/pa_shoot.png')
    }
};
