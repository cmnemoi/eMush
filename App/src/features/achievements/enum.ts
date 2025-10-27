import { getImgUrl } from "@/utils/getImgUrl";

export const enum StatisticEnum {
    ANDIE = "andie",
    CHAO = "chao",
    CHUN = "chun",
    FINOLA = "finola",
    CONTRIBUTIONS = "contributions",
    FRIEDA = "frieda",
    IAN = "ian",
    CAT_CUDDLED = "cat_cuddled",
    COFFEE_TAKEN = "coffee_taken",
    DOOR_REPAIRED = "door_repaired",
    EXPLO_FEED = "explo_feed",
    EXPLORER = "explorer",
    ARTEFACT_SPECIALIST = "artefact_specialist",
    BACK_TO_ROOT = "back_to_root",
    EXTINGUISH_FIRE = "extinguish_fire",
    GAGGED = "gagged",
    GIVE_MISSION = "give_mission",
    NEW_PLANTS = "new_plants",
    PLANET_SCANNED = "planet_scanned",
    SIGNAL_EQUIP = "signal_equip",
    SIGNAL_FIRE = "signal_fire",
    SUCCEEDED_INSPECTION = "succeeded_inspection",
}

export const StatisticRecords: {[index: string]: {icon: string}} = {
    [StatisticEnum.ANDIE]: {
        'icon': getImgUrl('char/head/andie.png')
    },
    [StatisticEnum.CHAO]: {
        'icon': getImgUrl('char/head/chao.png')
    },
    [StatisticEnum.CHUN]: {
        'icon': getImgUrl('char/head/chun.png')
    },
    [StatisticEnum.FINOLA]: {
        'icon': getImgUrl('char/head/finola.png')
    },
    [StatisticEnum.CONTRIBUTIONS]: {
        'icon': getImgUrl('ui_icons/action_points/pa_comp.png')
    },
    [StatisticEnum.FRIEDA]: {
        'icon': getImgUrl('char/head/frieda.png')
    },
    [StatisticEnum.IAN]: {
        'icon': getImgUrl('char/head/ian.png')
    },
    [StatisticEnum.CAT_CUDDLED]: {
        'icon': getImgUrl('achievements/cat.png')
    },
    [StatisticEnum.COFFEE_TAKEN]: {
        'icon': getImgUrl('achievements/coffeeplus.png')
    },
    [StatisticEnum.DOOR_REPAIRED]: {
        'icon': getImgUrl('alerts/door.png')
    },
    [StatisticEnum.EXPLO_FEED]: {
        'icon': getImgUrl('achievements/explo_feed.png')
    },
    [StatisticEnum.EXPLORER]: {
        'icon': getImgUrl('achievements/explorer.png')
    },
    [StatisticEnum.ARTEFACT_SPECIALIST]: {
        'icon': getImgUrl('achievements/artefact_specialist.png')
    },
    [StatisticEnum.BACK_TO_ROOT]: {
        'icon': getImgUrl('achievements/sol.png')
    },
    [StatisticEnum.EXTINGUISH_FIRE]: {
        'icon': getImgUrl('achievements/extinguish_fire.png')
    },
    [StatisticEnum.GAGGED]: {
        'icon': getImgUrl('status/gagged.png')
    },
    [StatisticEnum.GIVE_MISSION]: {
        'icon': getImgUrl('achievements/notebook.png')
    },
    [StatisticEnum.NEW_PLANTS]: {
        'icon': getImgUrl('status/plant_youngling.png')
    },
    [StatisticEnum.PLANET_SCANNED]: {
        'icon': getImgUrl('achievements/planet_scanned.png')
    },
    [StatisticEnum.SIGNAL_EQUIP]: {
        'icon': getImgUrl('achievements/reported.png')
    },
    [StatisticEnum.SIGNAL_FIRE]: {
        'icon': getImgUrl('achievements/signal_fire.png')
    },
    [StatisticEnum.SUCCEEDED_INSPECTION]: {
        'icon': getImgUrl('achievements/search.png')
    }
};
