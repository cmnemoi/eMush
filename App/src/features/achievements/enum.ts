import { getImgUrl } from "@/utils/getImgUrl";

export const enum StatisticEnum {
    ANDIE = "andie",
    CHAO = "chao",
    CHUN = "chun",
    FINOLA = "finola",
    FRIEDA = "frieda",
    JANICE = "janice",
    JIN_SU = "jin_su",
    CONTRIBUTIONS = "contributions",
    IAN = "ian",
    STEPHEN = "stephen",
    DEREK = "derek",
    ELEESHA = "eleesha",
    GIOELE = "gioele",
    HUA = "hua",
    KUAN_TI = "kuan_ti",
    PAOLA = "paola",
    RALUCA = "raluca",
    ROLAND = "roland",
    CAT_CUDDLED = "cat_cuddled",
    COFFEE_TAKEN = "coffee_taken",
    DOOR_REPAIRED = "door_repaired",
    EXPLO_FEED = "explo_feed",
    EXPLORER = "explorer",
    BACK_TO_ROOT = "back_to_root",
    CAMERA_INSTALLED = "camera_installed",
    EXTINGUISH_FIRE = "extinguish_fire",
    GAGGED = "gagged",
    GIVE_MISSION = "give_mission",
    NEW_PLANTS = "new_plants",
    GAME_WITHOUT_SLEEP = "game_without_sleep",
    PLANET_SCANNED = "planet_scanned",
    SIGNAL_EQUIP = "signal_equip",
    SIGNAL_FIRE = "signal_fire",
    SUCCEEDED_INSPECTION = "succeeded_inspection",
    COOKED_TAKEN = "cooked_taken",
    DAILY_ORDER = "daily_order",
    EDEN = "eden",
    MUSH_CYCLES = "mush_cycles",
    EDEN_CONTAMINATED = "eden_contaminated",
    POLITICIAN = "politician",
    HUNTER_DOWN = "hunter_down",
    LIKES = "likes",
    TERRENCE = "terrence",
    SURGEON = "surgeon",
    BUTCHER = "butcher",
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
    [StatisticEnum.FRIEDA]: {
        'icon': getImgUrl('char/head/frieda.png')
    },
    [StatisticEnum.JANICE]: {
        'icon': getImgUrl('char/head/janice.png')
    },
    [StatisticEnum.JIN_SU]: {
        'icon': getImgUrl('char/head/jin_su.png')
    },
    [StatisticEnum.CONTRIBUTIONS]: {
        'icon': getImgUrl('ui_icons/action_points/pa_comp.png')
    },
    [StatisticEnum.IAN]: {
        'icon': getImgUrl('char/head/ian.png')
    },
    [StatisticEnum.STEPHEN]: {
        'icon': getImgUrl('char/head/stephen.png')
    },
    [StatisticEnum.DEREK]: {
        'icon': getImgUrl('char/head/derek.png')
    },
    [StatisticEnum.ELEESHA]: {
        'icon': getImgUrl('char/head/eleesha.png')
    },
    [StatisticEnum.GIOELE]: {
        'icon': getImgUrl('char/head/gioele.png')
    },
    [StatisticEnum.HUA]: {
        'icon': getImgUrl('char/head/hua.png')
    },
    [StatisticEnum.KUAN_TI]: {
        'icon': getImgUrl('char/head/kuan_ti.png')
    },
    [StatisticEnum.PAOLA]: {
        'icon': getImgUrl('char/head/paola.png')
    },
    [StatisticEnum.RALUCA]: {
        'icon': getImgUrl('char/head/raluca.png')
    },
    [StatisticEnum.ROLAND]: {
        'icon': getImgUrl('char/head/roland.png')
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
    [StatisticEnum.BACK_TO_ROOT]: {
        'icon': getImgUrl('achievements/sol.png')
    },
    [StatisticEnum.CAMERA_INSTALLED]: {
        'icon': getImgUrl('achievements/camera.png')
    },
    [StatisticEnum.EXTINGUISH_FIRE]: {
        'icon': getImgUrl('achievements/signal_fire.png')
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
    [StatisticEnum.GAME_WITHOUT_SLEEP]: {
        'icon': getImgUrl('achievements/game_without_sleep.png')
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
    },
    [StatisticEnum.COOKED_TAKEN]: {
        'icon': getImgUrl('achievements/rationcooked.png')
    },
    [StatisticEnum.DAILY_ORDER]: {
        'icon': getImgUrl('achievements/notebook.png')
    },
    [StatisticEnum.EDEN]: {
        'icon': getImgUrl('ui_icons/eden1.png')
    },
    [StatisticEnum.MUSH_CYCLES]: {
        'icon': getImgUrl('char/head/mush.png')
    },
    [StatisticEnum.EDEN_CONTAMINATED]: {
        'icon': getImgUrl('ui_icons/eden2.png')
    },
    [StatisticEnum.POLITICIAN]: {
        'icon': getImgUrl('achievements/politician.png')
    },
    [StatisticEnum.HUNTER_DOWN]: {
        'icon': getImgUrl('alerts/hunter.png')
    },
    [StatisticEnum.LIKES]: {
        'icon': getImgUrl('achievements/likemush.gif')
    },
    [StatisticEnum.TERRENCE]: {
        'icon': getImgUrl('char/head/terrence.png')
    },
    [StatisticEnum.SURGEON]: {
        'icon': getImgUrl('achievements/escal.png')
    },
    [StatisticEnum.BUTCHER]: {
        'icon': getImgUrl('achievements/butcher.png')
    }
};
