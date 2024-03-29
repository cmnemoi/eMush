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
        'icon': '/src/assets/images/status/heavy.png',
    },
    [StatusItemNameEnum.HIDDEN]: {
        'icon': '/src/assets/images/status/hidden.png',
    },
    [StatusItemNameEnum.PLANT_YOUNG]: {
        'icon': '/src/assets/images/status/plant_youngling.png',
    },
    [StatusItemNameEnum.PLANT_THIRSTY]: {
        'icon': '/src/assets/images/status/plant_thirsty.png',
    },
    [StatusItemNameEnum.PLANT_DRY]: {
        'icon': '/src/assets/images/status/plant_dry.png',
    },
    [StatusItemNameEnum.PLANT_DISEASED]: {
        'icon': '/src/assets/images/status/plant_diseased.png',
    },
    [StatusItemNameEnum.ELECTRIC_CHARGE]: {
        'icon': '/src/assets/images/status/charge.png',
    },
    [StatusItemNameEnum.BROKEN]: {
        'icon': '/src/assets/images/status/broken.png',
    },
    [StatusItemNameEnum.FROZEN]: {
        'icon': '/src/assets/images/status/food_frozen.png',
    },
    [StatusItemNameEnum.UPDATING]: {
        'icon': '/src/assets/images/status/update.png',
    },
    [StatusItemNameEnum.UNSTABLE]: {
        'icon': '/src/assets/images/status/food_unstable.png',
    },
    [StatusItemNameEnum.HASARDOUS]: {
        'icon': '/src/assets/images/status/food_hazardous.png',
    },
    [StatusItemNameEnum.DECOMPOSING]: {
        'icon': '/src/assets/images/status/food_decaying.png',
    }
};
