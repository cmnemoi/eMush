const ARACK = 'spider';
const ASTEROID = 'asteroid';
const D1000 = 'dice';  
const HUNTER = 'hunter';
const TRAX = 'trax';

export const hunterEnum: {[index: string]: any} = {
    [ARACK]: {
        'image': require('@/assets/images/hunters/arack.png')
    },
    [ASTEROID]: {
        'image': require('@/assets/images/hunters/asteroid.png')
    },
    [D1000]: {
        'image': require('@/assets/images/hunters/d1000.png')
    },
    [HUNTER]: {
        'image': require('@/assets/images/hunters/hunter.png')
    },
    [TRAX]: {
        'image': require('@/assets/images/hunters/trax.png')
    },
};
