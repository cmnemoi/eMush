import {ItemsEnum} from '../../src/enums/items.enum';

export default {
    [ItemsEnum.STANDARD_RATION]: {
        name: 'ration standard',
        description:
            'Une portion de mélasse protéinée agglomérée dans une barquette spatiale longue-conservation. Ces rations sont étudiées pour être facile à digérer par voie traditionnelle mais peuvent également être injectées en intraveineuse.',
        examine:
            'Une portion de mélasse protéinée agglomérée dans une barquette spatiale longue-conservation. Ces rations sont étudiées pour être facile à digérer par voie traditionnelle mais peuvent également être injectées en intraveineuse.',
        genre: 'f',
    },
    [ItemsEnum.STAINPROOF_APRON]: {
        name: 'tablier intachable',
        description:
            'Vous ne vous salissez jamais en effectuant des actions salissantes.',
        examine:
            'Vous ne vous salissez jamais en effectuant des actions salissantes.\n' +
            'Un vieux tablier fatigué avec écrit dessus : "Meilleur ingénieur de mon coeur". Nul ne sait qui a ramené ça mais il peut servir...',
        genre: 'm',
    },
};
