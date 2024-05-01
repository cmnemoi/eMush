import { getImgUrl } from "@/utils/getImgUrl";

export enum SpecialistPointEnum {
    COMPUTER_SCIENTIST = "computerScientistPoint",
    COOKER = "cookerPoint",
    BOTANIST = "botanistPoint",
    DESIGNER = "designerPoint", // aka Neron project point
    ENGINEER = "engineerPoint",
    HEALER = "healerPoint",
    PHYSICAN = "physicanPoint",
    SHOOTER = "shootPoint",
}

export const specialistPointEnum: {[index: string]: any} = {
    [SpecialistPointEnum.SHOOTER]: {
        'icon': getImgUrl('pa_shoot.png')
    },
    [SpecialistPointEnum.ENGINEER]: {
        'icon': getImgUrl('pa_eng.png')
    }
};