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
    ARTEFACT_COLL = "artefact_coll",
    EXTINGUISH_FIRE = "extinguish_fire",
    GAGGED = "gagged",
    GIVE_MISSION = "give_mission",
    NEW_PLANTS = "new_plants",
    GAME_WITHOUT_SLEEP = "game_without_sleep",
    PLANET_SCANNED = "planet_scanned",
    SIGNAL_EQUIP = "signal_equip",
    SIGNAL_FIRE = "signal_fire",
    SUCCEEDED_INSPECTION = "succeeded_inspection",
    HAS_MUSHED = 'has_mushed',
    MUSHED = 'mushed',
    COOKED_TAKEN = "cooked_taken",
    DAILY_ORDER = "daily_order",
    EDEN = "eden",
    MUSH_CYCLES = "mush_cycles",
    EDEN_CONTAMINATED = "eden_contaminated",
    MAGE_BOOK_LEARNED = "mage_book_learned",
    NATAMIST = "natamist",
    POLITICIAN = "politician",
    HUNTER_DOWN = "hunter_down",
    LIKES = "likes",
    TERRENCE = "terrence",
    SURGEON = "surgeon",
    BUTCHER = "butcher",
    COMMUNICATION_EXPERT = "communication_expert",
    DAY_5_REACHED = "day_5_reached",
    DAY_10_REACHED = "day_10_reached",
    DAY_15_REACHED = "day_15_reached",
    DAY_20_REACHED = "day_20_reached",
    DAY_30_REACHED = "day_30_reached",
    MUSH_GENOME = "mush_genome",
    REBELS = "rebels",
    PILGRED_IS_BACK = "pilgred_is_back",
    COFFEE_MAN = "coffee_man",
    RATION_TAKEN = "ration_taken",
    DAY_MAX = "day_max",
    PROJECT_COMPLETE = "project_complete",
    RESEARCH_COMPLETE = "research_complete",
    DRUGS_TAKEN = "drugs_taken",
    KIVANC_CONTACTED = "kivanc_contacted",
    NILS_CONTACTED = "nils_contacted",
    ARTEFACT_SPECIALIST = "artefact_specialist",
    TEAM_ALL_REBELS = "team_all_rebels",
    TEAM_REBELS = 'team_rebels',
    PLASMA_SHIELD = "plasma_shield",
    GRENADIER = 'grenadier',
    FROZEN_TAKEN = 'frozen_taken',
    KIND_PERSON = 'kind_person',
    DISEASE_CONTRACTED = 'disease_contracted',
    MANKAROG_DOWN = 'mankarog_down',
    MUSH_KILLED = 'mush_killed',
    TEAM_MUSH_KILLED = 'team_mush_killed',
    PROJECT_TEAM = 'project_team',
    RESEARCH_TEAM = 'research_team',
    LAST_MEMBER = 'last_member',
    COMMANDER_SHOULD_GO_LAST = 'commander_should_go_last',
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
    [StatisticEnum.ARTEFACT_COLL]: {
        'icon': getImgUrl('status/artefact.png')
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
    [StatisticEnum.HAS_MUSHED]: {
        'icon': getImgUrl('achievements/infecter.png')
    },
    [StatisticEnum.MUSHED]: {
        'icon': getImgUrl('status/mush.png')
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
    [StatisticEnum.MAGE_BOOK_LEARNED]: {
        'icon': getImgUrl('achievements/learned.png')
    },
    [StatisticEnum.NATAMIST]: {
        'icon': getImgUrl('achievements/natamist.png')
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
    },
    [StatisticEnum.COMMUNICATION_EXPERT]: {
        'icon': getImgUrl('title_com_manager.png')
    },
    [StatisticEnum.DAY_5_REACHED]: {
        'icon': getImgUrl('achievements/day5.png')
    },
    [StatisticEnum.DAY_10_REACHED]: {
        'icon': getImgUrl('achievements/day10.png')
    },
    [StatisticEnum.DAY_15_REACHED]: {
        'icon': getImgUrl('achievements/day15.png')
    },
    [StatisticEnum.DAY_20_REACHED]: {
        'icon': getImgUrl('achievements/day20.png')
    },
    [StatisticEnum.DAY_30_REACHED]: {
        'icon': getImgUrl('achievements/day30.png')
    },
    [StatisticEnum.MUSH_GENOME]: {
        'icon': getImgUrl('achievements/mush_genome.png')
    },
    [StatisticEnum.REBELS]: {
        'icon': getImgUrl('achievements/5pillar.png')
    },
    [StatisticEnum.PILGRED_IS_BACK]: {
        'icon': getImgUrl('ui_icons/action_points/pa_pilgred.png')
    },
    [StatisticEnum.COFFEE_MAN]: {
        'icon': getImgUrl('ui_icons/coffee.png')
    },
    [StatisticEnum.RATION_TAKEN]: {
        'icon': getImgUrl('ui_icons/ration.png')
    },
    [StatisticEnum.DAY_MAX]: {
        'icon': getImgUrl('comms/calendar.png')
    },
    [StatisticEnum.PROJECT_COMPLETE]: {
        'icon': getImgUrl('achievements/conceptor.png')
    },
    [StatisticEnum.RESEARCH_COMPLETE]: {
        'icon': getImgUrl('achievements/research_digged.png')
    },
    [StatisticEnum.DRUGS_TAKEN]: {
        'icon': getImgUrl('ui_icons/pill.png')
    },
    [StatisticEnum.KIVANC_CONTACTED]: {
        'icon': getImgUrl('achievements/kivanc.png')
    },
    [StatisticEnum.NILS_CONTACTED]: {
        'icon': getImgUrl('achievements/nils.png')
    },
    [StatisticEnum.ARTEFACT_SPECIALIST]: {
        'icon': getImgUrl('achievements/collection.png')
    },
    [StatisticEnum.TEAM_ALL_REBELS]: {
        'icon': getImgUrl('achievements/revolution.png')
    },
    [StatisticEnum.TEAM_REBELS]: {
        'icon': getImgUrl('achievements/rebellion.png')
    },
    [StatisticEnum.PLASMA_SHIELD]: {
        'icon': getImgUrl('plasma.png')
    },
    [StatisticEnum.GRENADIER]: {
        'icon': getImgUrl('achievements/grenadier.png')
    },
    [StatisticEnum.FROZEN_TAKEN]: {
        'icon': getImgUrl('status/food_frozen.png')
    },
    [StatisticEnum.KIND_PERSON]: {
        'icon': getImgUrl('achievements/shrink_bed.png')
    },
    [StatisticEnum.DISEASE_CONTRACTED]: {
        'icon': getImgUrl('status/disease.png')
    },
    [StatisticEnum.MANKAROG_DOWN]: {
        'icon': getImgUrl('achievements/mankarog.png')
    },
    [StatisticEnum.MUSH_KILLED]: {
        'icon': getImgUrl('achievements/mush_kill.png')
    },
    [StatisticEnum.TEAM_MUSH_KILLED]: {
        'icon': getImgUrl('ui_icons/soldier.png')
    },
    [StatisticEnum.PROJECT_TEAM]: {
        'icon': getImgUrl('skills/human/conceptor.png')
    },
    [StatisticEnum.RESEARCH_TEAM]: {
        'icon': getImgUrl('ui_icons/research.png')
    },
    [StatisticEnum.LAST_MEMBER]: {
        'icon': getImgUrl('comms/fav.png')
    },
    [StatisticEnum.COMMANDER_SHOULD_GO_LAST]: {
        'icon': getImgUrl('title_commander.png')
    }
};
