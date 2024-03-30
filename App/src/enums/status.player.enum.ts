import { getAssetUrl } from '../utils/getAssetUrl';

export enum StatusPlayerNameEnum {
    STARVING = "starving",
    BURDENED = "burdened",
    DISABLED = "disabled",
    FULL_STOMACH = "full_stomach",
    IMMUNIZED = "immunized",
    LYING_DOWN = "lying_down",
    DIRTY = "dirty",
    MUSH = "mush",
    PACIFIST = "pacifist",
    DEMORALIZED = "demoralized",
    SUICIDAL = "suicidal",
    SPORES = "spores",
    ANTISOCIAL = "antisocial",
    FIRST_TIME = "first_time",
    PREGNANT = "pregnant",
    GAGGED = "gagged",
    FOCUSED = "focused",
    STUCK_IN_THE_SHIP = "stuck_in_the_ship",
    POC_SHOOTER_SKILL = "poc_shooter_skill",
    POC_PILOT_SKILL = "poc_pilot_skill",
    LOST = "lost"
};

export const statusPlayerEnum: {[index: string]: any} = {
    [StatusPlayerNameEnum.STARVING]: {
        'icon': getAssetUrl('status/starving.png'),
    },
    [StatusPlayerNameEnum.FIRST_TIME]: {
        'icon': getAssetUrl('status/first_time.png'),
    },
    [StatusPlayerNameEnum.BURDENED]: {
        'icon': getAssetUrl('status/heavy.png'),
    },
    [StatusPlayerNameEnum.DISABLED]: {
        'icon': getAssetUrl('status/disabled.png'),
    },
    [StatusPlayerNameEnum.FULL_STOMACH]: {
        'icon': getAssetUrl('status/belly_full.png'),
    },
    [StatusPlayerNameEnum.IMMUNIZED]: {
        'icon': getAssetUrl('status/immune.png'),
    },
    [StatusPlayerNameEnum.LYING_DOWN]: {
        'icon': getAssetUrl('status/laid.png'),
    },
    [StatusPlayerNameEnum.DIRTY]: {
        'icon': getAssetUrl('status/stinky.png'),
    },
    [StatusPlayerNameEnum.MUSH]: {
        'icon': getAssetUrl('status/mush.png'),
    },
    [StatusPlayerNameEnum.PACIFIST]: {
        'icon': getAssetUrl('status/pacifist.png'),
    },
    [StatusPlayerNameEnum.DEMORALIZED]: {
        'icon': getAssetUrl('status/demoralized.png'),
    },
    [StatusPlayerNameEnum.SUICIDAL]: {
        'icon': getAssetUrl('status/suicidal.png'),
    },
    [StatusPlayerNameEnum.SPORES]: {
        'icon': getAssetUrl('status/spore.png'),
    },
    [StatusPlayerNameEnum.ANTISOCIAL]: {
        'icon': getAssetUrl('status/unsociable.png'),
    },
    [StatusPlayerNameEnum.PREGNANT]: {
        'icon': getAssetUrl('status/pregnant.png'),
    },
    [StatusPlayerNameEnum.GAGGED]: {
        'icon': getAssetUrl('status/gagged.png'),
    },
    [StatusPlayerNameEnum.FOCUSED]: {
        'icon': getAssetUrl('status/moduling.png'),
    },
    [StatusPlayerNameEnum.STUCK_IN_THE_SHIP]: {
        'icon': getAssetUrl('status/stuck_in_ship.png'),
    },
    [StatusPlayerNameEnum.POC_PILOT_SKILL]: {
        'icon': getAssetUrl('skills/human/pilot.png'),
    },
    [StatusPlayerNameEnum.POC_SHOOTER_SKILL]: {
        'icon': getAssetUrl('skills/human/gunman.png'),
    },
    [StatusPlayerNameEnum.LOST]: {
        'icon': getAssetUrl('status/lost_on_planet.png'),
    }
};
