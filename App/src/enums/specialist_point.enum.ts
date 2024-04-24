import { getImgUrl } from '../utils/getImgUrl';

export enum SpecialistPointEnum {
    COMPUTER_SCIENTIST = "computer_scientist",
    COOKER = "cooker",
    BOTANIST = "botanist",
    DESIGNER = "designer", // Neron project point
    ENGINEER = "engineer",
    HEALER = "healer",
    PHYSICAN = "physican",
    SHOOTER = "shooter",
}

export const statusPlayerEnum: {[index: string]: any} = {
    [SpecialistPointEnum.SHOOTER]: {
        'icon': getImgUrl('pa_shoot.png')
    },
    [StatusPlayerNameEnum.ENGINEER]: {
        'icon': getImgUrl('pa_eng.png')
    },
}