const DEFAULT = "default";

export enum CharacterEnum {
    ANDIE = "andie",
    CHAO = "chao",
    CHUN = "chun",
    DEREK = "derek",
    ELEESHA = "eleesha",
    FINOLA = "finola",
    FRIEDA = "frieda",
    GIOELE = "gioele",
    HUA = "hua",
    IAN = "ian",
    JANICE = "janice",
    JIN_SU = "kim_jin_su",
    KUAN_TI = "kuan_ti",
    PAOLA = "paola",
    RALUCA = "raluca",
    ROLAND = "roland",
    STEPHEN = "stephen",
    TERRENCE = "terrence",
    NERON = "neron"
}

export interface CharacterInfos {
    head: string,
    body: string,
    portrait?: string,
    moveLeftFirstFrame?: number,
    moveLeftLastFrame?: number,
    leftFrame?: number,
    rightFrame?: number,
    moveRightFirstFrame?: number,
    moveRightLastFrame?: number,
};

export const characterEnum : {[index: string]: CharacterInfos}  = {
    [CharacterEnum.ANDIE]: {
        'head': require('@/assets/images/char/head/andie.png'),
        'body': require('@/assets/images/char/body/andie.png'),
        'portrait': require('@/assets/images/char/portrait/andie_graham_portrait.jpg'),
        'moveLeftFirstFrame': 1112,
        'moveLeftLastFrame': 1117,
        'leftFrame': 1103,
        'rightFrame': 1102,
        'moveRightFirstFrame': 1106,
        'moveRightLastFrame': 1111
    },
    [CharacterEnum.CHAO]: {
        'head': require('@/assets/images/char/head/chao.png'),
        'body': require('@/assets/images/char/body/chao.png'),
        'portrait': require('@/assets/images/char/portrait/Wang_chao_portrait.jpg'),
        'moveLeftFirstFrame': 213,
        'moveLeftLastFrame': 217,
        'leftFrame': 204,
        'rightFrame': 203,
        'moveRightFirstFrame': 208,
        'moveRightLastFrame': 212
    },
    [CharacterEnum.CHUN]: {
        'head': require('@/assets/images/char/head/chun.png'),
        'body': require('@/assets/images/char/body/chun.png'),
        'portrait': require('@/assets/images/char/portrait/Zhong_chun_portrait.jpg'),
        'moveLeftFirstFrame': 329,
        'moveLeftLastFrame': 333,
        'leftFrame': 320,
        'rightFrame': 319,
        'moveRightFirstFrame': 324,
        'moveRightLastFrame': 328
    },
    [CharacterEnum.DEREK]: {
        'head': require('@/assets/images/char/head/derek.png'),
        'body': require('@/assets/images/char/body/derek.png'),
        'portrait': require('@/assets/images/char/portrait/derek_hogan_portrait.jpg'),
        'moveLeftFirstFrame': 1083,
        'moveLeftLastFrame': 1088,
        'leftFrame': 1074,
        'rightFrame': 1073,
        'moveRightFirstFrame': 1077,
        'moveRightLastFrame': 1082
    },
    [CharacterEnum.ELEESHA]: {
        'head': require('@/assets/images/char/head/eleesha.png'),
        'body': require('@/assets/images/char/body/eleesha.png'),
        'portrait': require('@/assets/images/char/portrait/Eleesha_williams_portrait.jpg'),
        'moveLeftFirstFrame': 416,
        'moveLeftLastFrame': 420,
        'leftFrame': 407,
        'rightFrame': 406,
        'moveRightFirstFrame': 411,
        'moveRightLastFrame': 415
    },
    [CharacterEnum.FINOLA]: {
        'head': require('@/assets/images/char/head/finola.png'),
        'body': require('@/assets/images/char/body/finola.png'),
        'portrait': require('@/assets/images/char/portrait/Finola_keegan_portrait.jpg'),
        'moveLeftFirstFrame': 242,
        'moveLeftLastFrame': 246,
        'leftFrame': 233,
        'rightFrame': 232,
        'moveRightFirstFrame': 237,
        'moveRightLastFrame': 241
    },
    [CharacterEnum.FRIEDA]: {
        'head': require('@/assets/images/char/head/frieda.png'),
        'body': require('@/assets/images/char/body/frieda.png'),
        'portrait': require('@/assets/images/char/portrait/Frieda_bergmann_portrait.jpg'),
        'moveLeftFirstFrame': 39,
        'moveLeftLastFrame': 43,
        'leftFrame': 30,
        'rightFrame': 29,
        'moveRightFirstFrame': 34,
        'moveRightLastFrame': 38
    },
    [CharacterEnum.GIOELE]: {
        'head': require('@/assets/images/char/head/gioele.png'),
        'body': require('@/assets/images/char/body/gioele.png'),
        'portrait': require('@/assets/images/char/portrait/Gioele_rinaldo_portrait.jpg'),
        'moveLeftFirstFrame': 387,
        'moveLeftLastFrame': 391,
        'leftFrame': 378,
        'rightFrame': 377,
        'moveRightFirstFrame': 382,
        'moveRightLastFrame': 386
    },
    [CharacterEnum.HUA]: {
        'head': require('@/assets/images/char/head/hua.png'),
        'body': require('@/assets/images/char/body/hua.png'),
        'portrait': require('@/assets/images/char/portrait/Jiang_hua_portrait.jpg'),
        'moveLeftFirstFrame': 155,
        'moveLeftLastFrame': 159,
        'leftFrame': 146,
        'rightFrame': 145,
        'moveRightFirstFrame': 150,
        'moveRightLastFrame': 154
    },
    [CharacterEnum.IAN]: {
        'head': require('@/assets/images/char/head/ian.png'),
        'body': require('@/assets/images/char/body/ian.png'),
        'portrait': require('@/assets/images/char/portrait/Ian_soulton_portrait.jpg'),
        'moveLeftFirstFrame': 300,
        'moveLeftLastFrame': 304,
        'leftFrame': 291,
        'rightFrame': 290,
        'moveRightFirstFrame': 295,
        'moveRightLastFrame': 299
    },
    [CharacterEnum.JANICE]: {
        'head': require('@/assets/images/char/head/janice.png'),
        'body': require('@/assets/images/char/body/janice.png'),
        'portrait': require('@/assets/images/char/portrait/Janice_kent_portrait.jpg'),
        'moveLeftFirstFrame': 97,
        'moveLeftLastFrame': 101,
        'leftFrame': 88,
        'rightFrame': 87,
        'moveRightFirstFrame': 92,
        'moveRightLastFrame': 96
    },
    [CharacterEnum.JIN_SU]: {
        'head': require('@/assets/images/char/head/jin_su.png'),
        'body': require('@/assets/images/char/body/jin_su.png'),
        'portrait': require('@/assets/images/char/portrait/Kim_jin_su_portrait.jpg'),
        'moveLeftFirstFrame': 10,
        'moveLeftLastFrame': 15,
        'leftFrame': 1,
        'rightFrame': 0,
        'moveRightFirstFrame': 4,
        'moveRightLastFrame': 9
    },
    [CharacterEnum.KUAN_TI]: {
        'head': require('@/assets/images/char/head/kuan_ti.png'),
        'body': require('@/assets/images/char/body/kuan_ti.png'),
        'portrait': require('@/assets/images/char/portrait/Lai_kuan_ti_portrait.jpg'),
        'moveLeftFirstFrame': 68,
        'moveLeftLastFrame': 73,
        'leftFrame': 59,
        'rightFrame': 58,
        'moveRightFirstFrame': 62,
        'moveRightLastFrame': 67
    },
    [CharacterEnum.PAOLA]: {
        'head': require('@/assets/images/char/head/paola.png'),
        'body': require('@/assets/images/char/body/paola.png'),
        'portrait': require('@/assets/images/char/portrait/Paola_rinaldo_portrait.jpg'),
        'moveLeftFirstFrame': 184,
        'moveLeftLastFrame': 189,
        'leftFrame': 175,
        'rightFrame': 174,
        'moveRightFirstFrame': 178,
        'moveRightLastFrame': 183
    },
    [CharacterEnum.RALUCA]: {
        'head': require('@/assets/images/char/head/raluca.png'),
        'body': require('@/assets/images/char/body/raluca.png'),
        'portrait': require('@/assets/images/char/portrait/Raluca_tomescu_portrait.jpg'),
        'moveLeftFirstFrame': 358,
        'moveLeftLastFrame': 363,
        'leftFrame': 349,
        'rightFrame': 348,
        'moveRightFirstFrame': 352,
        'moveRightLastFrame': 357
    },
    [CharacterEnum.ROLAND]: {
        'head': require('@/assets/images/char/head/roland.png'),
        'body': require('@/assets/images/char/body/roland.png'),
        'portrait': require('@/assets/images/char/portrait/Roland_zuccali_portrait.jpg'),
        'moveLeftFirstFrame': 126,
        'moveLeftLastFrame': 131,
        'leftFrame': 117,
        'rightFrame': 116,
        'moveRightFirstFrame': 120,
        'moveRightLastFrame': 125
    },
    [CharacterEnum.STEPHEN]: {
        'head': require('@/assets/images/char/head/stephen.png'),
        'body': require('@/assets/images/char/body/stephen.png'),
        'portrait': require('@/assets/images/char/portrait/Stephen_seagull_portrait.jpg'),
        'moveLeftFirstFrame': 271,
        'moveLeftLastFrame': 276,
        'leftFrame': 262,
        'rightFrame': 261,
        'moveRightFirstFrame': 265,
        'moveRightLastFrame': 270
    },
    [CharacterEnum.TERRENCE]: {
        'head': require('@/assets/images/char/head/terrence.png'),
        'body': require('@/assets/images/char/body/terrence.png'),
        'portrait': require('@/assets/images/char/portrait/Terrence_archer_portrait.jpg'),
        'moveLeftFirstFrame': 445,
        'moveLeftLastFrame': 450,
        'leftFrame': 436,
        'rightFrame': 435,
        'moveRightFirstFrame': 439,
        'moveRightLastFrame': 444
    },
    [CharacterEnum.NERON]: {
        'head': require('@/assets/images/comms/neron_chat.png'),
        'body': require('@/assets/images/comms/neron_chat.png')
    },
    [DEFAULT]: {
        'head': require('@/assets/images/char/head/lambda_f.png'),
        'body': require('@/assets/images/char/body/lambda_f.png'),
        'moveLeftFirstFrame': 474,
        'moveLeftLastFrame': 479,
        'leftFrame': 465,
        'rightFrame': 464,
        'moveRightFirstFrame': 468,
        'moveRightLastFrame': 473
    }
}
;
