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
    [AlertEnum.NO_ALERT]: require('@/assets/images/alerts/infoalert.png'),
    [AlertEnum.LOW_OXYGEN]: require('@/assets/images/alerts/o2alert.png'),
    [AlertEnum.LOW_HULL]: require('@/assets/images/shield.png'),
    [AlertEnum.FIRES]: require('@/assets/images/alerts/fire.png'),
    [AlertEnum.BROKEN_DOORS]: require('@/assets/images/alerts/door.png'),
    [AlertEnum.BROKEN_EQUIPMENTS]: require('@/assets/images/alerts/broken.png'),
    [AlertEnum.NO_GRAVITY]: require('@/assets/images/alerts/simulator.png'),
    [AlertEnum.GRAVITY_REBOOT]: require('@/assets/images/alerts/simulatorReboot.png'),
    [AlertEnum.HUNGER]: require('@/assets/images/alerts/hunger.png'),
    [AlertEnum.HUNTER]: require('@/assets/images/alerts/hunter.png')
}
;
