import { getAssetUrl } from "@/utils/getAssetUrl";

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
    [AlertEnum.NO_ALERT]: getAssetUrl('alerts/infoalert.png'),
    [AlertEnum.LOW_OXYGEN]: getAssetUrl('alerts/o2alert.png'),
    [AlertEnum.LOW_HULL]: getAssetUrl('shield.png'),
    [AlertEnum.FIRES]: getAssetUrl('alerts/fire.png'),
    [AlertEnum.BROKEN_DOORS]: getAssetUrl('alerts/door.png'),
    [AlertEnum.BROKEN_EQUIPMENTS]: getAssetUrl('alerts/broken.png'),
    [AlertEnum.NO_GRAVITY]: getAssetUrl('alerts/simulator.png'),
    [AlertEnum.GRAVITY_REBOOT]: getAssetUrl('alerts/simulatorReboot.png'),
    [AlertEnum.HUNGER]: getAssetUrl('alerts/hunger.png'),
    [AlertEnum.HUNTER]: getAssetUrl('alerts/hunter.png'),
}
;
