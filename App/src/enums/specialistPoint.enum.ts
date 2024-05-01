import { getImgUrl } from "@/utils/getImgUrl";

export enum SpecialistPointEnum {
    BOTANIST = "gardenPoint",
    COMPUTER_SCIENTIST = "computerScientistPoint",
    COOKER = "cookerPoint",
    DESIGNER = "pilgredPoint",
    ENGINEER = "engineerPoint",
    HEALER = "healerPoint",
    PHYSICAN = "physicanPoint",
    SHOOTER = "shootPoint",
}

export const specialistPointEnum: {[index: string]: any} = {
    [SpecialistPointEnum.BOTANIST]: {
        'icon': getImgUrl('action_points/pa_garden.png')
    },
    [SpecialistPointEnum.COOKER]: {
        'icon': getImgUrl('action_points/pa_cook.png')
    },
    [SpecialistPointEnum.DESIGNER]: {
        'icon': getImgUrl('action_points/pa_pilgred.png')
    },
    [SpecialistPointEnum.ENGINEER]: {
        'icon': getImgUrl('action_points/pa_eng.png')
    },
    [SpecialistPointEnum.SHOOTER]: {
        'icon': getImgUrl('action_points/pa_shoot.png')
    },
};