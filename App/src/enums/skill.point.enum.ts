import { getImgUrl } from "@/utils/getImgUrl";

export enum SkillPointEnum {
    //IT Expert
    COMPUTER = 'computer',
    PA_COMP = 'pa_comp', //grandfathered in from formatText
    PA_IT = 'pa_it', //alternate spelling for players
    //Cook
    COOK = 'cook',
    PA_COOK = 'pa_cook', //grandfathered in from formatText
    //Designer
    CORE = 'core',
    PA_CORE = 'pa_core', //grandfathered in from formatText
    PA_DESIGN = 'pa_design', //alternate spelling for players
    //Technician
    ENGINEER = 'engineer',
    PA_ENG = 'pa_eng', //grandfathered in from formatText
    PA_TECH = 'pa_tech', //alternate spelling for players
    //Botanist
    GARDEN = 'garden',
    PA_GARDEN = 'pa_garden', //grandfathered in from formatText
    PA_PLANT = 'pa_plant', //alternate spelling for players
    //Nurse
    HEAL = 'heal',
    PA_HEAL = 'pa_heal', //grandfathered in from formatText
    PA_NURSE = 'pa_nurse', //alternate spelling for players
    //Physician
    PILGRED = 'pilgred',
    PA_PILGRED = 'pa_pilgred', //grandfathered in from formatText
    PA_PHYS = 'pa_phys', //alternate spelling for players
    //Shooter
    SHOOT = 'shoot',
    PA_SHOOT = 'pa_shoot', //grandfathered in from formatText
    PA_GUN = 'pa_gun', //alternate spelling for players
}

export const skillPointEnum: {[index: string]: any} = {
    [SkillPointEnum.COMPUTER]: {
        'icon': getImgUrl('ui_icons/action_points/pa_comp.png')
    },
    [SkillPointEnum.PA_COMP]: {
        'icon': getImgUrl('ui_icons/action_points/pa_comp.png')
    },
    [SkillPointEnum.PA_IT]: {
        'icon': getImgUrl('ui_icons/action_points/pa_comp.png')
    },
    [SkillPointEnum.COOK]: {
        'icon': getImgUrl('ui_icons/action_points/pa_cook.png')
    },
    [SkillPointEnum.PA_COOK]: {
        'icon': getImgUrl('ui_icons/action_points/pa_cook.png')
    },
    [SkillPointEnum.CORE]: {
        'icon': getImgUrl('ui_icons/action_points/pa_core.png')
    },
    [SkillPointEnum.PA_CORE]: {
        'icon': getImgUrl('ui_icons/action_points/pa_core.png')
    },
    [SkillPointEnum.PA_DESIGN]: {
        'icon': getImgUrl('ui_icons/action_points/pa_core.png')
    },
    [SkillPointEnum.ENGINEER]: {
        'icon': getImgUrl('ui_icons/action_points/pa_tech.png')
    },
    [SkillPointEnum.PA_ENG]: {
        'icon': getImgUrl('ui_icons/action_points/pa_tech.png')
    },
    [SkillPointEnum.PA_TECH]: {
        'icon': getImgUrl('ui_icons/action_points/pa_tech.png')
    },
    [SkillPointEnum.GARDEN]: {
        'icon': getImgUrl('ui_icons/action_points/pa_garden.png')
    },
    [SkillPointEnum.PA_GARDEN]: {
        'icon': getImgUrl('ui_icons/action_points/pa_garden.png')
    },
    [SkillPointEnum.PA_PLANT]: {
        'icon': getImgUrl('ui_icons/action_points/pa_garden.png')
    },
    [SkillPointEnum.HEAL]: {
        'icon': getImgUrl('ui_icons/action_points/pa_heal.png')
    },
    [SkillPointEnum.PA_HEAL]: {
        'icon': getImgUrl('ui_icons/action_points/pa_heal.png')
    },
    [SkillPointEnum.PA_NURSE]: {
        'icon': getImgUrl('ui_icons/action_points/pa_heal.png')
    },
    [SkillPointEnum.PILGRED]: {
        'icon': getImgUrl('ui_icons/action_points/pa_pilgred.png')
    },
    [SkillPointEnum.PA_PILGRED]: {
        'icon': getImgUrl('ui_icons/action_points/pa_pilgred.png')
    },
    [SkillPointEnum.PA_PHYS]: {
        'icon': getImgUrl('ui_icons/action_points/pa_pilgred.png')
    },
    [SkillPointEnum.SHOOT]: {
        'icon': getImgUrl('ui_icons/action_points/pa_shoot.png')
    },
    [SkillPointEnum.PA_SHOOT]: {
        'icon': getImgUrl('ui_icons/action_points/pa_shoot.png')
    },
    [SkillPointEnum.PA_GUN]: {
        'icon': getImgUrl('ui_icons/action_points/pa_shoot.png')
    }
};
