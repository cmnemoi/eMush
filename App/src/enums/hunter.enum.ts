import { getImgUrl } from '@/utils/getImgUrl';

export const HUNTER_COLUMN_SIZE = 6;
export const HUNTER_COLUMN_COUNT = 2;

export enum HunterKeyEnum {
    ARACK = 'spider',
    ASTEROID = 'asteroid',
    D1000 = 'dice',
    HUNTER = 'hunter',
    TRAX = 'trax',
    TRANSPORT = 'transport',
}

export const HunterRankEnum: {[index: string]: number} = {
    [HunterKeyEnum.TRANSPORT]: 0,
    [HunterKeyEnum.HUNTER]: 1,
    [HunterKeyEnum.TRAX]: 2,
    [HunterKeyEnum.ARACK]: 3,
    [HunterKeyEnum.D1000]: 4,
    [HunterKeyEnum.ASTEROID]: 5
};


export enum HunterImageKeyEnum {
    ARACK = 'spider',
    ASTEROID = 'asteroid',
    D1000 = 'dice',
    HUNTER = 'hunter',
    TRAX = 'trax',
    TRANSPORT_1 = 'transport_1',
    TRANSPORT_2 = 'transport_2',
    TRANSPORT_3 = 'transport_3',
    TRANSPORT_4 = 'transport_4'
}

export const HunterImageEnum: {[index: string]: string} = {
    [HunterImageKeyEnum.ARACK]: getImgUrl('hunters/arack.png'),
    [HunterImageKeyEnum.ASTEROID]: getImgUrl('hunters/asteroid.png'),
    [HunterImageKeyEnum.D1000]: getImgUrl('hunters/d1000.png'),
    [HunterImageKeyEnum.HUNTER]: getImgUrl('hunters/hunter.png'),
    [HunterImageKeyEnum.TRANSPORT_1]: getImgUrl('hunters/transport_1.png'),
    [HunterImageKeyEnum.TRANSPORT_2]: getImgUrl('hunters/transport_2.png'),
    [HunterImageKeyEnum.TRANSPORT_3]: getImgUrl('hunters/transport_3.png'),
    [HunterImageKeyEnum.TRANSPORT_4]: getImgUrl('hunters/transport_4.png'),
    [HunterImageKeyEnum.TRAX]: getImgUrl('hunters/trax.png')

};
