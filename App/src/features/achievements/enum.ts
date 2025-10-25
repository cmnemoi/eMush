import { getImgUrl } from "@/utils/getImgUrl";

export const enum StatisticEnum {
    EXTINGUISH_FIRE = "extinguish_fire",
    GAGGED = "gagged",
    PLANET_SCANNED = "planet_scanned",
    SIGNAL_EQUIP = "signal_equip",
    SIGNAL_FIRE = "signal_fire",
}

export const StatisticRecords: {[index: string]: {icon: string}} = {
    [StatisticEnum.EXTINGUISH_FIRE]: {
        'icon': getImgUrl('achievements/signal_fire.png')
    },
    [StatisticEnum.GAGGED]: {
        'icon': getImgUrl('status/gagged.png')
    },
    [StatisticEnum.PLANET_SCANNED]: {
        'icon': getImgUrl('achievements/planet_scanned.png')
    },
    [StatisticEnum.SIGNAL_EQUIP]: {
        'icon': getImgUrl('achievements/reported.png')
    },
    [StatisticEnum.SIGNAL_FIRE]: {
        'icon': getImgUrl('achievements/signal_fire.png')
    }
};
