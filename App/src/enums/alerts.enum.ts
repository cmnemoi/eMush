export const NO_ALERT = 'no_alert';
const GRAVITY_REBOOT = 'gravity_reboot';
const LOW_OXYGEN = 'low_oxygen';
const LOW_HULL = 'low_hull';
const FIRES = 'fires';
const BROKEN_DOORS = 'broken_doors';
const BROKEN_EQUIPMENTS = 'broken_equipments';
const NO_GRAVITY = 'no_gravity';
const HUNGER = 'hunger';
const HUNTER = 'hunter';

export const AlertsIcons: {[index: string]: string} = {
    [NO_ALERT]: 'src/assets/images/alerts/infoalert.png',
    [LOW_OXYGEN]: 'src/assets/images/alerts/o2alert.png',
    [LOW_HULL]: 'src/assets/images/shield.png',
    [FIRES]: 'src/assets/images/alerts/fire.png',
    [BROKEN_DOORS]: 'src/assets/images/alerts/door.png',
    [BROKEN_EQUIPMENTS]: 'src/assets/images/alerts/broken.png',
    [NO_GRAVITY]: 'src/assets/images/alerts/simulator.png',
    [GRAVITY_REBOOT]: 'src/assets/images/alerts/simulatorReboot.png',
    [HUNGER]: 'src/assets/images/alerts/hunger.png',
    [HUNTER]: 'src/assets/images/alerts/hunter.png'
}
;
