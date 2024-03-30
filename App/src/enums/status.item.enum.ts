import { getAssetUrl } from "@/utils/getAssetUrl";

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
        'icon': getAssetUrl('status/heavy.png'),
    },
    [StatusItemNameEnum.HIDDEN]: {
        'icon': getAssetUrl('status/hidden.png'),
    },
    [StatusItemNameEnum.PLANT_YOUNG]: {
        'icon': getAssetUrl('status/plant_youngling.png'),
    },
    [StatusItemNameEnum.PLANT_THIRSTY]: {
        'icon': getAssetUrl('status/plant_thirsty.png'),
    },
    [StatusItemNameEnum.PLANT_DRY]: {
        'icon': getAssetUrl('status/plant_dry.png'),
    },
    [StatusItemNameEnum.PLANT_DISEASED]: {
        'icon': getAssetUrl('status/plant_diseased.png'),
    },
    [StatusItemNameEnum.ELECTRIC_CHARGE]: {
        'icon': getAssetUrl('status/charge.png'),
    },
    [StatusItemNameEnum.BROKEN]: {
        'icon': getAssetUrl('status/broken.png'),
    },
    [StatusItemNameEnum.FROZEN]: {
        'icon': getAssetUrl('status/food_frozen.png'),
    },
    [StatusItemNameEnum.UPDATING]: {
        'icon': getAssetUrl('status/update.png'),
    },
    [StatusItemNameEnum.UNSTABLE]: {
        'icon': getAssetUrl('status/food_unstable.png'),
    },
    [StatusItemNameEnum.HASARDOUS]: {
        'icon': getAssetUrl('status/food_hazardous.png'),
    },
    [StatusItemNameEnum.DECOMPOSING]: {
        'icon': getAssetUrl('status/food_decaying.png'),
    }
};
