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
    FOCUSED = "focused"
};

export const statusPlayerEnum: {[index: string]: any} = {
    [StatusPlayerNameEnum.STARVING]: {
        'icon': 'src/assets/images/status/starving.png'
    },
    [StatusPlayerNameEnum.FIRST_TIME]: {
        'icon': 'src/assets/images/status/first_time.png'
    },
    [StatusPlayerNameEnum.BURDENED]: {
        'icon': 'src/assets/images/status/heavy.png'
    },
    [StatusPlayerNameEnum.DISABLED]: {
        'icon': 'src/assets/images/status/disabled.png'
    },
    [StatusPlayerNameEnum.FULL_STOMACH]: {
        'icon': 'src/assets/images/status/belly_full.png'
    },
    [StatusPlayerNameEnum.IMMUNIZED]: {
        'icon': 'src/assets/images/status/immune.png'
    },
    [StatusPlayerNameEnum.LYING_DOWN]: {
        'icon': 'src/assets/images/status/laid.png'
    },
    [StatusPlayerNameEnum.DIRTY]: {
        'icon': 'src/assets/images/status/stinky.png'
    },
    [StatusPlayerNameEnum.MUSH]: {
        'icon': 'src/assets/images/status/mush.png'
    },
    [StatusPlayerNameEnum.PACIFIST]: {
        'icon': 'src/assets/images/status/pacifist.png'
    },
    [StatusPlayerNameEnum.DEMORALIZED]: {
        'icon': 'src/assets/images/status/demoralized.png'
    },
    [StatusPlayerNameEnum.SUICIDAL]: {
        'icon': 'src/assets/images/status/suicidal.png'
    },
    [StatusPlayerNameEnum.SPORES]: {
        'icon': 'src/assets/images/status/spore.png'
    },
    [StatusPlayerNameEnum.ANTISOCIAL]: {
        'icon': 'src/assets/images/status/unsociable.png'
    },
    [StatusPlayerNameEnum.PREGNANT]: {
        'icon': 'src/assets/images/status/pregnant.png'
    },
    [StatusPlayerNameEnum.GAGGED]: {
        'icon': 'src/assets/images/status/gagged.png'
    },
    [StatusPlayerNameEnum.FOCUSED]: {
        'icon': 'src/assets/images/status/moduling.png'
    }
};
