import { getImgUrl } from '../utils/getImgUrl';

const ARACK = 'spider';
const ASTEROID = 'asteroid';
const D1000 = 'dice';
const HUNTER = 'hunter';
const TRAX = 'trax';

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
    [TRAX]: {
        'image': getImgUrl('hunters/trax.png')
    }
};
