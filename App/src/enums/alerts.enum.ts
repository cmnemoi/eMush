export enum AlertEnum {
    NO_ALERT = 'no_alert',
    GRAVITY_REBOOT = 'gravity_reboot',
    LOW_OXYGEN = 'low_oxygen',
    LOW_HULL = 'low_hull',
    FIRES = 'fires',
    BROKEN_DOORS = 'broken_doors',
    BROKEN_EQUIPMENTS = 'broken_equipments',
    NO_GRAVITY = 'no_gravity',
    HUNGER = 'hunger',
    HUNTER = 'hunter',
}

export const AlertsIcons: {[index: string]: string} = {
    [AlertEnum.NO_ALERT]: '/src/assets/images/alerts/infoalert.png',
    [AlertEnum.LOW_OXYGEN]: '/src/assets/images/alerts/o2alert.png',
    [AlertEnum.LOW_HULL]: '/src/assets/images/shield.png',
    [AlertEnum.FIRES]: '/src/assets/images/alerts/fire.png',
    [AlertEnum.BROKEN_DOORS]: '/src/assets/images/alerts/door.png',
    [AlertEnum.BROKEN_EQUIPMENTS]: '/src/assets/images/alerts/broken.png',
    [AlertEnum.NO_GRAVITY]: '/src/assets/images/alerts/simulator.png',
    [AlertEnum.GRAVITY_REBOOT]: '/src/assets/images/alerts/simulatorReboot.png',
    [AlertEnum.HUNGER]: '/src/assets/images/alerts/hunger.png',
    [AlertEnum.HUNTER]: '/src/assets/images/alerts/hunter.png',
}
;
