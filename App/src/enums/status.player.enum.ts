const STARVING = "starving";
const BURDENED = "burdened";
const DISABLED = "disabled";
const FULL_STOMACH = "full_stomach";
const IMMUNIZED = "immunized";
const LYING_DOWN = "lying_down";
const DIRTY = "dirty";
const MUSH = "mush";
const PACIFIST = "pacifist";
const DEMORALIZED = "demoralized";
const SUICIDAL = "suicidal";
const SPORES = "spores";
const ANTISOCIAL = "antisocial";
const FIRST_TIME = "first_time";
const PREGNANT = "pregnant";
const GAGGED = "gagged";
const FOCUSED = "focused";


export const statusPlayerEnum: {[index: string]: any} = {
    [STARVING]: {
        'icon': require('@/assets/images/status/starving.png')
    },
    [FIRST_TIME]: {
        'icon': require('@/assets/images/status/first_time.png')
    },
    [BURDENED]: {
        'icon': require('@/assets/images/status/heavy.png')
    },
    [DISABLED]: {
        'icon': require('@/assets/images/status/disabled.png')
    },
    [FULL_STOMACH]: {
        'icon': require('@/assets/images/status/belly_full.png')
    },
    [IMMUNIZED]: {
        'icon': require('@/assets/images/status/immune.png')
    },
    [LYING_DOWN]: {
        'icon': require('@/assets/images/status/laid.png')
    },
    [DIRTY]: {
        'icon': require('@/assets/images/status/stinky.png')
    },
    [MUSH]: {
        'icon': require('@/assets/images/status/mush.png')
    },
    [PACIFIST]: {
        'icon': require('@/assets/images/status/pacifist.png')
    },
    [DEMORALIZED]: {
        'icon': require('@/assets/images/status/demoralized.png')
    },
    [SUICIDAL]: {
        'icon': require('@/assets/images/status/suicidal.png')
    },
    [SPORES]: {
        'icon': require('@/assets/images/status/spore.png')
    },
    [ANTISOCIAL]: {
        'icon': require('@/assets/images/status/unsociable.png')
    },
    [FIRST_TIME]: {
        'icon': require('@/assets/images/status/first_time.png')
    },
    [PREGNANT]: {
        'icon': require('@/assets/images/status/pregnant.png')
    },
    [GAGGED]: {
        'icon': require('@/assets/images/status/gagged.png')
    },
    [FOCUSED]: {
        'icon': require('@/assets/images/status/moduling.png')
    }
};
