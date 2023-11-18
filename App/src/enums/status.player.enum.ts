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
    STUCK_IN_THE_SHIP = "stuck_in_the_ship"
};

export const statusPlayerEnum: {[index: string]: any} = {
    [StatusPlayerNameEnum.STARVING]: {
        'icon': require('@/assets/images/status/starving.png')
    },
    [StatusPlayerNameEnum.FIRST_TIME]: {
        'icon': require('@/assets/images/status/first_time.png')
    },
    [StatusPlayerNameEnum.BURDENED]: {
        'icon': require('@/assets/images/status/heavy.png')
    },
    [StatusPlayerNameEnum.DISABLED]: {
        'icon': require('@/assets/images/status/disabled.png')
    },
    [StatusPlayerNameEnum.FULL_STOMACH]: {
        'icon': require('@/assets/images/status/belly_full.png')
    },
    [StatusPlayerNameEnum.IMMUNIZED]: {
        'icon': require('@/assets/images/status/immune.png')
    },
    [StatusPlayerNameEnum.LYING_DOWN]: {
        'icon': require('@/assets/images/status/laid.png')
    },
    [StatusPlayerNameEnum.DIRTY]: {
        'icon': require('@/assets/images/status/stinky.png')
    },
    [StatusPlayerNameEnum.MUSH]: {
        'icon': require('@/assets/images/status/mush.png')
    },
    [StatusPlayerNameEnum.PACIFIST]: {
        'icon': require('@/assets/images/status/pacifist.png')
    },
    [StatusPlayerNameEnum.DEMORALIZED]: {
        'icon': require('@/assets/images/status/demoralized.png')
    },
    [StatusPlayerNameEnum.SUICIDAL]: {
        'icon': require('@/assets/images/status/suicidal.png')
    },
    [StatusPlayerNameEnum.SPORES]: {
        'icon': require('@/assets/images/status/spore.png')
    },
    [StatusPlayerNameEnum.ANTISOCIAL]: {
        'icon': require('@/assets/images/status/unsociable.png')
    },
    [StatusPlayerNameEnum.PREGNANT]: {
        'icon': require('@/assets/images/status/pregnant.png')
    },
    [StatusPlayerNameEnum.GAGGED]: {
        'icon': require('@/assets/images/status/gagged.png')
    },
    [StatusPlayerNameEnum.FOCUSED]: {
        'icon': require('@/assets/images/status/moduling.png')
    },
    [StatusPlayerNameEnum.STUCK_IN_THE_SHIP]: {
        'icon': require('@/assets/images/status/stuck_in_ship.png')
    }
};
