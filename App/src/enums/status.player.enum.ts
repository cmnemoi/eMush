import { getImgUrl } from '../utils/getImgUrl';

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
        'icon': getImgUrl('status/starving.png')
    },
    [StatusPlayerNameEnum.FIRST_TIME]: {
        'icon': getImgUrl('status/first_time.png')
    },
    [StatusPlayerNameEnum.BURDENED]: {
        'icon': getImgUrl('status/heavy.png')
    },
    [StatusPlayerNameEnum.DISABLED]: {
        'icon': getImgUrl('status/disabled.png')
    },
    [StatusPlayerNameEnum.FULL_STOMACH]: {
        'icon': getImgUrl('status/belly_full.png')
    },
    [StatusPlayerNameEnum.IMMUNIZED]: {
        'icon': getImgUrl('status/immune.png')
    },
    [StatusPlayerNameEnum.LYING_DOWN]: {
        'icon': getImgUrl('status/laid.png')
    },
    [StatusPlayerNameEnum.DIRTY]: {
        'icon': getImgUrl('status/stinky.png')
    },
    [StatusPlayerNameEnum.MUSH]: {
        'icon': getImgUrl('status/mush.png')
    },
    [StatusPlayerNameEnum.PACIFIST]: {
        'icon': getImgUrl('status/pacifist.png')
    },
    [StatusPlayerNameEnum.DEMORALIZED]: {
        'icon': getImgUrl('status/demoralized.png')
    },
    [StatusPlayerNameEnum.SUICIDAL]: {
        'icon': getImgUrl('status/suicidal.png')
    },
    [StatusPlayerNameEnum.SPORES]: {
        'icon': getImgUrl('status/spore.png')
    },
    [StatusPlayerNameEnum.ANTISOCIAL]: {
        'icon': getImgUrl('status/unsociable.png')
    },
    [StatusPlayerNameEnum.PREGNANT]: {
        'icon': getImgUrl('status/pregnant.png')
    },
    [StatusPlayerNameEnum.GAGGED]: {
        'icon': getImgUrl('status/gagged.png')
    },
    [StatusPlayerNameEnum.FOCUSED]: {
        'icon': getImgUrl('status/moduling.png')
    },
    [StatusPlayerNameEnum.STUCK_IN_THE_SHIP]: {
        'icon': getImgUrl('status/stuck_in_ship.png')
    },
    [StatusPlayerNameEnum.POC_PILOT_SKILL]: {
        'icon': getImgUrl('skills/human/pilot.png')
    },
    [StatusPlayerNameEnum.POC_SHOOTER_SKILL]: {
        'icon': getImgUrl('skills/human/gunman.png')
    },
    [StatusPlayerNameEnum.LOST]: {
        'icon': getImgUrl('status/lost_on_planet.png')
    }
};
