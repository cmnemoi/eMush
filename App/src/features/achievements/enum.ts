import { getImgUrl } from "@/utils/getImgUrl";

export const enum StatisticEnum {
    EXTINGUISH_FIRE = "extinguish_fire",
    PLANET_SCANNED = "planet_scanned",
    SIGNAL_FIRE = "signal_fire",
}

export const StatisticRecords: {[index: string]: {icon: string}} = {
    [StatisticEnum.EXTINGUISH_FIRE]: {
        'icon': getImgUrl('achievements/signal_fire.png')
    },
    [StatisticEnum.PLANET_SCANNED]: {
        'icon': getImgUrl('achievements/planet_scanned.png')
    },
    [StatisticEnum.SIGNAL_FIRE]: {
        'icon': getImgUrl('achievements/signal_fire.png')
    }
};
