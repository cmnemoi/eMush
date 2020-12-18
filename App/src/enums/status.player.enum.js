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


export const statusPlayerEnum = {
    [STARVING]: {
        'icon': require('@/assets/images/status/starving.png'),
    },
    [BURDENED]: {
        'icon': require('@/assets/images/status/starving.png'),//@TODO
    },
    [DISABLED]: {
        'icon': require('@/assets/images/status/disabled.png'),
    },
    [FULL_STOMACH]: {
        'icon': require('@/assets/images/status/belly_full.png'),
    },
    [IMMUNIZED]: {
        'icon': require('@/assets/images/status/immune.png'),
    },
    [LYING_DOWN]: {
        'icon': require('@/assets/images/status/sleepy.png'),
    },
    [DIRTY]: {
        'icon': require('@/assets/images/status/stinky.png'),
    },
    [MUSH]: {
        'icon': require('@/assets/images/status/mush.png'),
    },
    [PACIFIST]: {
        'icon': require('@/assets/images/status/pacifist.png'),
    },
    [DEMORALIZED]: {
        'icon': require('@/assets/images/status/demoralized.png'),
    },
    [SUICIDAL]: {
        'icon': require('@/assets/images/status/suicidal.png'),
    },
}