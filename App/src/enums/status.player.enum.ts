import { getImgUrl } from '../utils/getImgUrl';

export enum StatusPlayerNameEnum {
    STARVING = "starving",
    STARVING_WARNING = "starving_warning",
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
    CONCEPTOR = "conceptor",
    PILOT = "pilot",
    SHOOTER = "shooter",
    TECHNICIAN = "technician",
    LOST = "lost",
    BERZERK = "berzerk",
    INACTIVE = "inactive",
    HIGHLY_INACTIVE = "highly_inactive",
    GUARDIAN = "guardian",
    GENIUS_IDEA = "genius_idea",
    PARIAH = "pariah",
};

export const statusPlayerEnum: {[index: string]: any} = {
    [StatusPlayerNameEnum.STARVING]: {
        'icon': getImgUrl('status/starving.png')
    },
    [StatusPlayerNameEnum.STARVING_WARNING]: {
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
    [StatusPlayerNameEnum.CONCEPTOR]: {
        'icon': getImgUrl('skills/human/conceptor.png')
    },
    [StatusPlayerNameEnum.PILOT]: {
        'icon': getImgUrl('skills/human/pilot.png')
    },
    [StatusPlayerNameEnum.SHOOTER]: {
        'icon': getImgUrl('skills/human/gunman.png')
    },
    [StatusPlayerNameEnum.TECHNICIAN]: {
        'icon': getImgUrl('skills/human/engineer.png')
    },
    [StatusPlayerNameEnum.LOST]: {
        'icon': getImgUrl('status/lost_on_planet.png')
    },
    [StatusPlayerNameEnum.BERZERK]: {
        'icon': getImgUrl('status/berzerk.png')
    },
    [StatusPlayerNameEnum.INACTIVE]: {
        'icon': getImgUrl('status/sleepy.png')
    },
    [StatusPlayerNameEnum.HIGHLY_INACTIVE]: {
        'icon': getImgUrl('status/noob.png')
    },
    [StatusPlayerNameEnum.GUARDIAN]: {
        'icon': getImgUrl('status/guardian.png')
    },
    [StatusPlayerNameEnum.GENIUS_IDEA]: {
        'icon': getImgUrl('status/genius_idea.png')
    },
    [StatusPlayerNameEnum.PARIAH]: {
        'icon': getImgUrl('status/unsociable.png')
    }
};
