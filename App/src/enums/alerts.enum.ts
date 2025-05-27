import { getImgUrl } from "@/utils/getImgUrl";

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
    OUTCAST = 'outcast',
}

export const AlertsIcons: {[index: string]: string} = {
    [AlertEnum.NO_ALERT]: getImgUrl('alerts/infoalert.png'),
    [AlertEnum.LOW_OXYGEN]: getImgUrl('alerts/o2alert.png'),
    [AlertEnum.LOW_HULL]: getImgUrl('shield.png'),
    [AlertEnum.FIRES]: getImgUrl('alerts/fire.png'),
    [AlertEnum.BROKEN_DOORS]: getImgUrl('alerts/door.png'),
    [AlertEnum.BROKEN_EQUIPMENTS]: getImgUrl('alerts/broken.png'),
    [AlertEnum.NO_GRAVITY]: getImgUrl('alerts/simulator.png'),
    [AlertEnum.GRAVITY_REBOOT]: getImgUrl('alerts/simulatorReboot.png'),
    [AlertEnum.HUNGER]: getImgUrl('alerts/hunger.png'),
    [AlertEnum.HUNTER]: getImgUrl('alerts/hunter.png'),
    [AlertEnum.OUTCAST]: getImgUrl('status/unsociable.png')
}
;
