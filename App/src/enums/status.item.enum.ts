import { getImgUrl } from "@/utils/getImgUrl";

export enum StatusItemNameEnum {
    ALIEN_ARTEFACT = "alien_artefact",
    HEAVY = "heavy",
    HIDDEN = "hidden",
    PLANT_YOUNG = "plant_young",
    PLANT_THIRSTY = "plant_thirsty",
    PLANT_DRY = "plant_dry",
    PLANT_DISEASED = "plant_diseased",
    ELECTRIC_CHARGE = "electric_charges",
    BROKEN = "broken",
    FROZEN = "frozen",
    UPDATING = "updating",
    UNSTABLE = "unstable",
    HASARDOUS = "hazardous",
    DECOMPOSING = "decomposing",
    CONTAMINATED = "contaminated",
    CAT_INFECTED = "cat_infected",
}

export const statusItemEnum: {[index: string]: any} = {
    [StatusItemNameEnum.ALIEN_ARTEFACT]: {
        'icon': getImgUrl('status/artefact.png')
    },
    [StatusItemNameEnum.HEAVY]: {
        'icon': getImgUrl('status/heavy.png')
    },
    [StatusItemNameEnum.HIDDEN]: {
        'icon': getImgUrl('status/hidden.png')
    },
    [StatusItemNameEnum.PLANT_YOUNG]: {
        'icon': getImgUrl('status/plant_youngling.png')
    },
    [StatusItemNameEnum.PLANT_THIRSTY]: {
        'icon': getImgUrl('status/plant_thirsty.png')
    },
    [StatusItemNameEnum.PLANT_DRY]: {
        'icon': getImgUrl('status/plant_dry.png')
    },
    [StatusItemNameEnum.PLANT_DISEASED]: {
        'icon': getImgUrl('status/plant_diseased.png')
    },
    [StatusItemNameEnum.ELECTRIC_CHARGE]: {
        'icon': getImgUrl('status/charge.png')
    },
    [StatusItemNameEnum.BROKEN]: {
        'icon': getImgUrl('status/broken.png')
    },
    [StatusItemNameEnum.FROZEN]: {
        'icon': getImgUrl('status/food_frozen.png')
    },
    [StatusItemNameEnum.UPDATING]: {
        'icon': getImgUrl('status/update.png')
    },
    [StatusItemNameEnum.UNSTABLE]: {
        'icon': getImgUrl('status/food_unstable.png')
    },
    [StatusItemNameEnum.HASARDOUS]: {
        'icon': getImgUrl('status/food_hazardous.png')
    },
    [StatusItemNameEnum.DECOMPOSING]: {
        'icon': getImgUrl('status/food_decaying.png')
    },
    [StatusItemNameEnum.CONTAMINATED]: {
        'icon': getImgUrl('status/spore.png')
    },
    [StatusItemNameEnum.CAT_INFECTED]: {
        'icon': getImgUrl('status/mush.png')
    }
};
export const StatusItemPriorityEnum: {[index: string]: number} = {
    [StatusItemNameEnum.CAT_INFECTED]: 1,
    [StatusItemNameEnum.CONTAMINATED]: 2,
    [StatusItemNameEnum.HIDDEN]: 3,
    [StatusItemNameEnum.BROKEN]: 4,
    [StatusItemNameEnum.HEAVY]: 5,
    [StatusItemNameEnum.PLANT_YOUNG]: 6,
    [StatusItemNameEnum.PLANT_DISEASED]: 7,
    [StatusItemNameEnum.PLANT_DRY]: 8,
    [StatusItemNameEnum.PLANT_THIRSTY]: 9,
    [StatusItemNameEnum.DECOMPOSING]: 10,
    [StatusItemNameEnum.FROZEN]: 11,
    [StatusItemNameEnum.ELECTRIC_CHARGE]: 12,
    [StatusItemNameEnum.ALIEN_ARTEFACT]: 13
};
