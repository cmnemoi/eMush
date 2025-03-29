import { getImgUrl } from '../utils/getImgUrl';

const ARACK = 'spider';
const ASTEROID = 'asteroid';
const D1000 = 'dice';
const HUNTER = 'hunter';
const TRAX = 'trax';
const TRANSPORT_1 = 'transport_1';
const TRANSPORT_2 = 'transport_2';
const TRANSPORT_3 = 'transport_3';
const TRANSPORT_4 = 'transport_4';

export const hunterEnum: {[index: string]: any} = {
    [ARACK]: {
        'image': getImgUrl('hunters/arack.png')
    },
    [ASTEROID]: {
        'image': getImgUrl('hunters/asteroid.png')
    },
    [D1000]: {
        'image': getImgUrl('hunters/d1000.png')
    },
    [HUNTER]: {
        'image': getImgUrl('hunters/hunter.png')
    },
    [TRANSPORT_1]: {
        'image': getImgUrl('hunters/transport_1.png')
    },
    [TRANSPORT_2]: {
        'image': getImgUrl('hunters/transport_2.png')
    },
    [TRANSPORT_3]: {
        'image': getImgUrl('hunters/transport_3.png')
    },
    [TRANSPORT_4]: {
        'image': getImgUrl('hunters/transport_4.png')
    },
    [TRAX]: {
        'image': getImgUrl('hunters/trax.png')
    }
};
