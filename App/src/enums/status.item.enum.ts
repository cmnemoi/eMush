export enum StatusItemNameEnum {
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
    DECOMPOSING = "decomposing"
}

export const statusItemEnum: {[index: string]: any} = {
    [StatusItemNameEnum.HEAVY]: {
        'icon': require('@/assets/images/status/heavy.png')
    },
    [StatusItemNameEnum.HIDDEN]: {
        'icon': require('@/assets/images/status/hidden.png')
    },
    [StatusItemNameEnum.PLANT_YOUNG]: {
        'icon': require('@/assets/images/status/plant_youngling.png')
    },
    [StatusItemNameEnum.PLANT_THIRSTY]: {
        'icon': require('@/assets/images/status/plant_thirsty.png')
    },
    [StatusItemNameEnum.PLANT_DRY]: {
        'icon': require('@/assets/images/status/plant_dry.png')
    },
    [StatusItemNameEnum.PLANT_DISEASED]: {
        'icon': require('@/assets/images/status/plant_diseased.png')
    },
    [StatusItemNameEnum.ELECTRIC_CHARGE]: {
        'icon': require('@/assets/images/status/charge.png')
    },
    [StatusItemNameEnum.BROKEN]: {
        'icon': require('@/assets/images/status/broken.png')
    },
    [StatusItemNameEnum.FROZEN]: {
        'icon': require('@/assets/images/status/food_frozen.png')
    },
    [StatusItemNameEnum.UPDATING]: {
        'icon': require('@/assets/images/status/update.png')
    },
    [StatusItemNameEnum.UNSTABLE]: {
        'icon': require('@/assets/images/status/food_unstable.png')
    },
    [StatusItemNameEnum.HASARDOUS]: {
        'icon': require('@/assets/images/status/food_hazardous.png')
    },
    [StatusItemNameEnum.DECOMPOSING]: {
        'icon': require('@/assets/images/status/food_decaying.png')
    }
};
