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
    WALL_BOX = "wall_box",
    PNEUMATIC_DISTRIBUTOR = "pneumatic_distributor",
    TYPE_REPLACE = "type_replace",
    TYPE_HIDE = "type_hide",
    TYPE_SHOW = "type_show",
}

export interface SkinInfo {
    key: string,
    frameChanges: Array<{ type: string, initialFrame: string, newFrame: string }>,
    animationChange?: [{ duration: number, tileid: number }]
}

// Skins are applied in alphabetical order of the skin slots
export const skinEnum : {[index: string]: SkinInfo}  = {
    [SkinEnum.JANICE_SEXY]: {
        key: 'janiceSexy',
        frameChanges: [{ type: SkinEnum.TYPE_REPLACE, initialFrame: 'janice', newFrame: 'janice_sexy' }],
        animationChange: undefined,
    },
    [SkinEnum.NERON_CORE_PARTICIPATION]: {
        key: 'neron_core_participation',
        frameChanges: [{ type: SkinEnum.TYPE_REPLACE, initialFrame: 'neron_core', newFrame: 'neron_core-participation' }],
        animationChange: undefined
    },
    [SkinEnum.NERON_PARTICIPATION_AUXILIARY_TERMINAL]: {
        key: 'neron_participation_auxiliary_terminal',
        frameChanges: [{ type: SkinEnum.TYPE_REPLACE, initialFrame: 'neron_terminal', newFrame: 'neron_terminal-participation' }],
        animationChange: undefined
    },
    [SkinEnum.ICARUS_THRUSTER]: {
        key: 'icarus_thruster',
        frameChanges: [{ type: SkinEnum.TYPE_REPLACE, initialFrame: 'icarus', newFrame: 'icarus-thrusters' }],
        animationChange: undefined
    },
    [SkinEnum.ICARUS_LARGE]: {
        key: 'icarus_large',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'icarus', newFrame: 'icarus-large' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'icarus-thrusters', newFrame: 'icarus-thrusters-large' }
        ],
        animationChange: undefined
    },
    [SkinEnum.ICARUS_RED_BLACK]: {
        key: 'icarus_red_black',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'icarus', newFrame: 'icarus-red_black' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'icarus-thrusters', newFrame: 'icarus-thrusters-red_black' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'icarus-large', newFrame: 'icarus-large-red_black' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'icarus-thrusters-large', newFrame: 'icarus-thrusters-large-red_black' }
        ],
        animationChange: undefined
    },
    [SkinEnum.KITCHEN_APERO]: {
        key: 'kitchen_apero',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'kitchen_part_1', newFrame: 'kitchen_part_1-apero' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'kitchen_part_2', newFrame: 'kitchen_part_2-apero' }
        ],
        animationChange: undefined
    },
    [SkinEnum.ANTENNA_SPATIAL_WAVE]: {
        key: 'antenna_spatial_waves',
        frameChanges: [{ type: SkinEnum.TYPE_REPLACE, initialFrame: 'antenna', newFrame: 'antenna_spatial_waves' }],
        animationChange: undefined
    },
    [SkinEnum.TURRET_TESLA]: {
        key: 'turret_tesla',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'alpha_turret-north', newFrame: 'alpha_turret-north-tesla' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'alpha_turret-west', newFrame: 'alpha_turret-west-tesla' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'bravo_turret-south', newFrame: 'bravo_turret-south-tesla' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'bravo_turret-east', newFrame: 'bravo_turret-east-tesla' }
        ],
        animationChange: undefined
    },
    [SkinEnum.TURRET_CHARGES]: {
        key: 'turret_charges',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'alpha_turret-north', newFrame: 'alpha_turret-north-charges' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'alpha_turret-west', newFrame: 'alpha_turret-west-charges' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'bravo_turret-south', newFrame: 'bravo_turret-south-charges' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'bravo_turret-east', newFrame: 'bravo_turret-east-charges' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'alpha_turret-north-tesla', newFrame: 'alpha_turret-north-tesla-charges' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'alpha_turret-west-tesla', newFrame: 'alpha_turret-west-tesla-charges' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'bravo_turret-south-tesla', newFrame: 'bravo_turret-south-tesla-charges' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'bravo_turret-east-tesla', newFrame: 'bravo_turret-east-tesla-charges' }
        ],
        animationChange: undefined
    },
    [SkinEnum.COFFEE_MACHINE_FISSION]: {
        key: 'coffee_machine_fission',
        frameChanges: [{ type: SkinEnum.TYPE_REPLACE, initialFrame: 'coffee_machine', newFrame: 'coffee_machine-fission' }],
        animationChange: undefined
    },
    [SkinEnum.COFFEE_MACHINE_GUARANA]: {
        key: 'coffee_machine_guarana',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'coffee_machine', newFrame: 'coffee_machine-guarana' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'coffee_machine-fission', newFrame: 'coffee_machine-fission-guarana' }
        ],
        animationChange: undefined
    },
    [SkinEnum.PATROL_SHIP_TELSATRON]: {
        key: 'patrol_ship_telsatron',
        frameChanges: [{ type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship', newFrame: 'patrol_ship-telsatron' }],
        animationChange: undefined
    },
    [SkinEnum.PATROL_SHIP_BLASTER]: {
        key: 'patrol_ship_blaster',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship', newFrame: 'patrol_ship-blaster' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship-blaster', newFrame: 'patrol_ship-blaster-telsatron' }
        ],
        animationChange: undefined
    },
    [SkinEnum.PATROL_SHIP_GOLD]: {
        key: 'patrol_ship_gold',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship', newFrame: 'patrol_ship-gold' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship-blaster', newFrame: 'patrol_ship-gold-blaster' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship-telsatron', newFrame: 'patrol_ship-gold-telsatron' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship-blaster-telsatron', newFrame: 'patrol_ship-gold-blaster-telsatron' }
        ],
        animationChange: undefined
    },
    [SkinEnum.PATROL_SHIP_RED_BLACK]: {
        key: 'patrol_ship_red_black',
        frameChanges: [
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship', newFrame: 'patrol_ship-red_black' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship-blaster', newFrame: 'patrol_ship-red_black-blaster' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship-telsatron', newFrame: 'patrol_ship-red_black-telsatron' },
            { type: SkinEnum.TYPE_REPLACE, initialFrame: 'patrol_ship-blaster-telsatron', newFrame: 'patrol_ship-red_black-blaster-telsatron' }
        ],
        animationChange: undefined
    },
};
