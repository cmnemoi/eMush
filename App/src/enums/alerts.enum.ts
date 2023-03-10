export const NO_ALERT = 'no_alert';
const LOW_OXYGEN = 'low_oxygen';
const LOW_HULL = 'low_hull';
const FIRES = 'fires';
const BROKEN_DOORS = 'broken_doors';
const BROKEN_EQUIPMENTS = 'broken_equipments';
const NO_GRAVITY = 'no_gravity';
const HUNGER = 'hunger';

export const AlertsIcons: {[index: string]: string} = {
    [NO_ALERT]: require('@/assets/images/alerts/infoalert.png'),
    [LOW_OXYGEN]: require('@/assets/images/alerts/o2alert.png'),
    [LOW_HULL]: require('@/assets/images/shield.png'),
    [FIRES]: require('@/assets/images/alerts/fire.png'),
    [BROKEN_DOORS]: require('@/assets/images/alerts/door.png'),
    [BROKEN_EQUIPMENTS]: require('@/assets/images/alerts/broken.png'),
    [NO_GRAVITY]: require('@/assets/images/alerts/simulator.png'),
    [HUNGER]: require('@/assets/images/alerts/hunger.png')
}
;
