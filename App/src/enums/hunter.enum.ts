import { getAssetUrl } from '../utils/getAssetUrl';

const ARACK = 'spider';
const ASTEROID = 'asteroid';
const D1000 = 'dice';
const HUNTER = 'hunter';
const TRAX = 'trax';

export const hunterEnum: {[index: string]: any} = {
    [ARACK]: {
        'image': getAssetUrl('hunters/arack.png'),
    },
    [ASTEROID]: {
        'image': getAssetUrl('hunters/asteroid.png'),
    },
    [D1000]: {
        'image': getAssetUrl('hunters/d1000.png'),
    },
    [HUNTER]: {
        'image': getAssetUrl('hunters/hunter.png'),
    },
    [TRAX]: {
        'image': getAssetUrl('hunters/trax.png'),
    }
};
