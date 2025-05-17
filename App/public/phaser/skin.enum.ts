export enum SkinEnum {
    JANICE_SEXY = "janice_sexy",
    IAN_LABCOAT = "ian_labcoat",
    CHAO_LEATHER = "chao_leather",
    ROLAND_YULE = "roland_yule",
    ELEESHA_CLASSY = "eleesha_classy",
    RALUCA_ADDAMS = "raluca_addams",
    TERRENCE_BIKER = "terrence_biker",
    JIN_SU_GNANGNAN = "jin_su_gnangnan",
    HUA_PUPKIN = "hua_pupkin",
    JIN_SU_VAMPIRE = "jin_su_vampire",
    STEPHEN_SANTA = "stephen_santa",
    PAOLA_SANTA = "paola_santa",
    KUAN_ALBATOR = "kuan_albator",
    FINOLA_SKYWALKER = "finola_skywalker",
    GIOELE_BATHROBE = "gioele_bathrobe",
    CHUN_DALLAS = "chun_dallas",
    FRIEDA_GONADALF = "frieda_gonadalf",

    REACTOR_BROKEN ="reactor_broken",
    BAY_DOOR_EXTRALARGE = "bay_door_extralarge",
    GARDEN_INCUBATOR = "garden_incubator",
    GARDEN_LAMP = "garden_lamp",
    KITCHEN_APERO = 'kitchen_apero',
    PILGRED_ACTIVE = "pilgred_active",
    PLASMA_SHIELD_ACTIVE = "plasma_shield_active",
    SCANNER_OVERCLOCKING = "scanner_overclocking",
    ANTENNA_SPATIAL_WAVE = "antenna_spatial_wave",
    TURRET_TESLA = "turret_tesla",
    TURRET_CHARGES = "turret_charges",
    COFFEE_MACHINE_FISSION = "coffee_machine_fission",
    COFFEE_MACHINE_GUARANA = "coffee_machine_guarana",
    ICARUS_LARGE = "icarus_large",
    ICARUS_THRUSTER = "icarus_thruster",
    ICARUS_RED_BLACK = "icarus_red_black",
    NERON_CORE_PARTICIPATION = "neron_core_participation",
    NERON_PARTICIPATION_AUXILIARY_TERMINAL = "neron_participation_auxiliary_terminal",
    PATROL_SHIP_BLASTER = "patrol_ship_blaster",
    PATROL_SHIP_GOLD = "patrol_ship_gold",
    PATROL_SHIP_TELSATRON = "patrol_ship_telsatron",
    PATROL_SHIP_RED_BLACK = "patrol_ship_red_black",
    SOFA_BROWN = "sofa_brown",
    SOFA_GREEN = "sofa_green",
    SOFA_PINK = "sofa_pink",
    SOFA_WHITE = "sofa_white",

    ALPHA_POSTER = "alpha_poster",
    TABLE_APERO = "table_apero",
    MAGNETIC_NET = "magnetic_net",
    MAGNETIC_RETURN = "magnetic_return",
    QUANTUM_SENSOR = "quantum_sensor",
    TAKEOFF_PLATFORM_PROPULSION = "takeoff_platform_propulsion",
    AUTO_WATERING = "auto_watering",
    PNEUMATIC_DISTRIBUTOR = "pneumatic_distributor",
    TYPE_REPLACE = "type_replace",
    TYPE_HIDE = "type_hide",
    TYPE_SHOW = "type_show",
}


export interface FrameTransformation {
    initialFrame: string,
    newFrame: string
}
export interface SkinInfo {
    key: string,
    type: string,
    frameChanges: { [index: string]: FrameTransformation },
    animationChange?: [{ duration: number, tileid: number }]
}

// Skins are applied in alphabetical order of the skin slots
export const skinEnum: { [index: string]: SkinInfo } = {
    [SkinEnum.JANICE_SEXY]: {
        key: 'janiceSexy',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['janice']: { initialFrame: 'janice', newFrame: 'janice_sexy' }
        },
        animationChange: undefined
    },
    [SkinEnum.NERON_CORE_PARTICIPATION]: {
        key: 'neron_core_participation',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['neron_core']: { initialFrame: 'neron_core', newFrame: 'neron_core-participation' }
        },
        animationChange: undefined
    },
    [SkinEnum.NERON_PARTICIPATION_AUXILIARY_TERMINAL]: {
        key: 'neron_participation_auxiliary_terminal',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['neron_terminal']: { initialFrame: 'neron_terminal', newFrame: 'neron_terminal-participation' }
        },
        animationChange: undefined
    },
    [SkinEnum.ICARUS_THRUSTER]: {
        key: 'icarus_thruster',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['icarus']: { initialFrame: 'icarus', newFrame: 'icarus-thrusters' }
        },
        animationChange: undefined
    },
    [SkinEnum.ICARUS_LARGE]: {
        key: 'icarus_large',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['icarus']: { initialFrame: 'icarus', newFrame: 'icarus-large' },
            ['icarus-thrusters']: { initialFrame: 'icarus-thrusters', newFrame: 'icarus-thrusters-large' }
        },
        animationChange: undefined
    },
    [SkinEnum.ICARUS_RED_BLACK]: {
        key: 'icarus_red_black',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['icarus']: { initialFrame: 'icarus', newFrame: 'icarus-red_black' },
            ['icarus-thrusters']: { initialFrame: 'icarus-thrusters', newFrame: 'icarus-thrusters-red_black' },
            ['icarus-large']: { initialFrame: 'icarus-large', newFrame: 'icarus-large-red_black' },
            ['icarus-thrusters-large']: { initialFrame: 'icarus-thrusters-large', newFrame: 'icarus-thrusters-large-red_black' }
        },
        animationChange: undefined
    },
    [SkinEnum.KITCHEN_APERO]: {
        key: 'kitchen_apero',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['kitchen_part_1']: { initialFrame: 'kitchen_part_1', newFrame: 'kitchen_part_1-apero' },
            ['kitchen_part_2']: { initialFrame: 'kitchen_part_2', newFrame: 'kitchen_part_2-apero' }
        },
        animationChange: undefined
    },
    [SkinEnum.ANTENNA_SPATIAL_WAVE]: {
        key: 'antenna_spatial_waves',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['antenna']: { initialFrame: 'antenna', newFrame: 'antenna_spatial_waves' }
        },
        animationChange: undefined
    },
    [SkinEnum.TURRET_TESLA]: {
        key: 'turret_tesla',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['alpha_turret-north']: { initialFrame: 'alpha_turret-north', newFrame: 'alpha_turret-north-tesla' },
            ['alpha_turret-west']: { initialFrame: 'alpha_turret-west', newFrame: 'alpha_turret-west-tesla' },
            ['bravo_turret-south']: { initialFrame: 'bravo_turret-south', newFrame: 'bravo_turret-south-tesla' },
            ['bravo_turret-east']: { initialFrame: 'bravo_turret-east', newFrame: 'bravo_turret-east-tesla' }
        },
        animationChange: undefined
    },
    [SkinEnum.TURRET_CHARGES]: {
        key: 'turret_charges',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['alpha_turret-north']: { initialFrame: 'alpha_turret-north', newFrame: 'alpha_turret-north-charges' },
            ['alpha_turret-west']: { initialFrame: 'alpha_turret-west', newFrame: 'alpha_turret-west-charges' },
            ['bravo_turret-south']: { initialFrame: 'bravo_turret-south', newFrame: 'bravo_turret-south-charges' },
            ['bravo_turret-east']: { initialFrame: 'bravo_turret-east', newFrame: 'bravo_turret-east-charges' },
            ['alpha_turret-north-tesla']: { initialFrame: 'alpha_turret-north-tesla', newFrame: 'alpha_turret-north-tesla-charges' },
            ['alpha_turret-west-tesla']: { initialFrame: 'alpha_turret-west-tesla', newFrame: 'alpha_turret-west-tesla-charges' },
            ['bravo_turret-south-tesla']: { initialFrame: 'bravo_turret-south-tesla', newFrame: 'bravo_turret-south-tesla-charges' },
            ['bravo_turret-east-tesla']: { initialFrame: 'bravo_turret-east-tesla', newFrame: 'bravo_turret-east-tesla-charges' }
        },
        animationChange: undefined
    },
    [SkinEnum.COFFEE_MACHINE_FISSION]: {
        key: 'coffee_machine_fission',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['coffee_machine']: { initialFrame: 'coffee_machine', newFrame: 'coffee_machine-fission' }
        },
        animationChange: undefined
    },
    [SkinEnum.COFFEE_MACHINE_GUARANA]: {
        key: 'coffee_machine_guarana',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['coffee_machine']: { initialFrame: 'coffee_machine', newFrame: 'coffee_machine-guarana' },
            ['coffee_machine-fission']: { initialFrame: 'coffee_machine-fission', newFrame: 'coffee_machine-fission-guarana' }
        },
        animationChange: undefined
    },
    [SkinEnum.PATROL_SHIP_TELSATRON]: {
        key: 'patrol_ship_telsatron',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['patrol_ship']: { initialFrame: 'patrol_ship', newFrame: 'patrol_ship-telsatron' }
        },
        animationChange: undefined
    },
    [SkinEnum.PATROL_SHIP_BLASTER]: {
        key: 'patrol_ship_blaster',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['patrol_ship']: { initialFrame: 'patrol_ship', newFrame: 'patrol_ship-blaster' },
            ['patrol_ship-blaster']: { initialFrame: 'patrol_ship-blaster', newFrame: 'patrol_ship-blaster-telsatron' }
        },
        animationChange: undefined
    },
    [SkinEnum.PATROL_SHIP_GOLD]: {
        key: 'patrol_ship_gold',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['patrol_ship']: { initialFrame: 'patrol_ship', newFrame: 'patrol_ship-gold' },
            ['patrol_ship-blaster']: { initialFrame: 'patrol_ship-blaster', newFrame: 'patrol_ship-gold-blaster' },
            ['patrol_ship-telsatron']: { initialFrame: 'patrol_ship-telsatron', newFrame: 'patrol_ship-gold-telsatron' },
            ['patrol_ship-blaster-telsatron']: { initialFrame: 'patrol_ship-blaster-telsatron', newFrame: 'patrol_ship-gold-blaster-telsatron' }
        },
        animationChange: undefined
    },
    [SkinEnum.PATROL_SHIP_RED_BLACK]: {
        key: 'patrol_ship_red_black',
        type: SkinEnum.TYPE_REPLACE,
        frameChanges: {
            ['patrol_ship']: { initialFrame: 'patrol_ship', newFrame: 'patrol_ship-red_black' },
            ['patrol_ship-blaster']: { initialFrame: 'patrol_ship-blaster', newFrame: 'patrol_ship-red_black-blaster' },
            ['patrol_ship-telsatron']: { initialFrame: 'patrol_ship-telsatron', newFrame: 'patrol_ship-red_black-telsatron' },
            ['patrol_ship-blaster-telsatron']: { initialFrame: 'patrol_ship-blaster-telsatron', newFrame: 'patrol_ship-red_black-blaster-telsatron' }
        },
        animationChange: undefined
    },
    [SkinEnum.AUTO_WATERING]: {
        key: 'auto_watering',
        type: SkinEnum.TYPE_SHOW,
        frameChanges: {
            ['wall_box']: { initialFrame: 'wall_box', newFrame: 'wall_box' }
        },
        animationChange: undefined
    }
};
