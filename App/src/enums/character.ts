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
    JIN_SU = "jin_su",
    KUAN_TI = "kuan_ti",
    PAOLA = "paola",
    RALUCA = "raluca",
    ROLAND = "roland",
    STEPHEN = "stephen",
    TERRENCE = "terrence",
    NERON = "neron"
}

export interface CharacterInfos {
    name?: string,
    completeName?: string,
    head: string,
    body: string,
    portrait?: string,
};

export const characterEnum : {[index: string]: CharacterInfos}  = {
    [CharacterEnum.ANDIE]: {
        'name': 'Andie',
        'completeName': 'Andie Graham',
        'head': require('@/assets/images/char/head/andie.png'),
        'body': require('@/assets/images/char/body/andie.png'),
        'portrait': require('@/assets/images/char/portrait/andie_graham_portrait.jpg')
    },
    [CharacterEnum.CHAO]: {
        'name': 'Chao',
        'completeName': 'Wang Chao',
        'head': require('@/assets/images/char/head/chao.png'),
        'body': require('@/assets/images/char/body/chao.png'),
        'portrait': require('@/assets/images/char/portrait/Wang_chao_portrait.jpg')
    },
    [CharacterEnum.CHUN]: {
        'name': 'Chun',
        'completeName': 'Zhong Chun',
        'head': require('@/assets/images/char/head/chun.png'),
        'body': require('@/assets/images/char/body/chun.png'),
        'portrait': require('@/assets/images/char/portrait/Zhong_chun_portrait.jpg')
    },
    [CharacterEnum.DEREK]: {
        'name': 'Derek',
        'completeName': 'Derek Hogan',
        'head': require('@/assets/images/char/head/derek.png'),
        'body': require('@/assets/images/char/body/derek.png'),
        'portrait': require('@/assets/images/char/portrait/derek_hogan_portrait.jpg')
    },
    [CharacterEnum.ELEESHA]: {
        'name': 'Eleesha',
        'completeName': 'Eleesha Williams',
        'head': require('@/assets/images/char/head/eleesha.png'),
        'body': require('@/assets/images/char/body/eleesha.png'),
        'portrait': require('@/assets/images/char/portrait/Eleesha_williams_portrait.jpg')
    },
    [CharacterEnum.FINOLA]: {
        'name': 'Finola',
        'completeName': 'Finola Keegan',
        'head': require('@/assets/images/char/head/finola.png'),
        'body': require('@/assets/images/char/body/finola.png'),
        'portrait': require('@/assets/images/char/portrait/Finola_keegan_portrait.jpg')
    },
    [CharacterEnum.FRIEDA]: {
        'name': 'Frieda',
        'completeName': 'Frieda Bergmann',
        'head': require('@/assets/images/char/head/frieda.png'),
        'body': require('@/assets/images/char/body/frieda.png'),
        'portrait': require('@/assets/images/char/portrait/Frieda_bergmann_portrait.jpg')
    },
    [CharacterEnum.GIOELE]: {
        'name': 'Gioele',
        'completeName': 'Gioele Rinaldo',
        'head': require('@/assets/images/char/head/gioele.png'),
        'body': require('@/assets/images/char/body/gioele.png'),
        'portrait': require('@/assets/images/char/portrait/Gioele_rinaldo_portrait.jpg')
    },
    [CharacterEnum.HUA]: {
        'name': 'Hua',
        'completeName': 'Jiang Hua',
        'head': require('@/assets/images/char/head/hua.png'),
        'body': require('@/assets/images/char/body/hua.png'),
        'portrait': require('@/assets/images/char/portrait/Jiang_hua_portrait.jpg')
    },
    [CharacterEnum.IAN]: {
        'name': 'Ian',
        'completeName': 'Ian Soulton',
        'head': require('@/assets/images/char/head/ian.png'),
        'body': require('@/assets/images/char/body/ian.png'),
        'portrait': require('@/assets/images/char/portrait/Ian_soulton_portrait.jpg')
    },
    [CharacterEnum.JANICE]: {
        'name': 'Janice',
        'completeName': 'Janice Kent',
        'head': require('@/assets/images/char/head/janice.png'),
        'body': require('@/assets/images/char/body/janice.png'),
        'portrait': require('@/assets/images/char/portrait/Janice_kent_portrait.jpg')
    },
    [CharacterEnum.JIN_SU]: {
        'name': 'Jin Su',
        'completeName': 'Kim Jin Su',
        'head': require('@/assets/images/char/head/jin_su.png'),
        'body': require('@/assets/images/char/body/jin_su.png'),
        'portrait': require('@/assets/images/char/portrait/Kim_jin_su_portrait.jpg')
    },
    [CharacterEnum.KUAN_TI]: {
        'name': 'Kuan Ti',
        'completeName': 'Lai Kuan Ti',
        'head': require('@/assets/images/char/head/kuan_ti.png'),
        'body': require('@/assets/images/char/body/kuan_ti.png'),
        'portrait': require('@/assets/images/char/portrait/Lai_kuan_ti_portrait.jpg')
    },
    [CharacterEnum.PAOLA]: {
        'name': 'Paola',
        'completeName': 'Paola Rinaldo',
        'head': require('@/assets/images/char/head/paola.png'),
        'body': require('@/assets/images/char/body/paola.png'),
        'portrait': require('@/assets/images/char/portrait/Paola_rinaldo_portrait.jpg')
    },
    [CharacterEnum.RALUCA]: {
        'name': 'Raluca',
        'completeName': 'Raluca Tomescu',
        'head': require('@/assets/images/char/head/raluca.png'),
        'body': require('@/assets/images/char/body/raluca.png'),
        'portrait': require('@/assets/images/char/portrait/Raluca_tomescu_portrait.jpg')
    },
    [CharacterEnum.ROLAND]: {
        'name': 'Roland',
        'completeName': 'Roland Zuccali',
        'head': require('@/assets/images/char/head/roland.png'),
        'body': require('@/assets/images/char/body/roland.png'),
        'portrait': require('@/assets/images/char/portrait/Roland_zuccali_portrait.jpg')
    },
    [CharacterEnum.STEPHEN]: {
        'name': 'Stephen',
        'completeName': 'Stephen Seagull',
        'head': require('@/assets/images/char/head/stephen.png'),
        'body': require('@/assets/images/char/body/stephen.png'),
        'portrait': require('@/assets/images/char/portrait/Stephen_seagull_portrait.jpg')
    },
    [CharacterEnum.TERRENCE]: {
        'name': 'Terrence',
        'completeName': 'Terrence Archer',
        'head': require('@/assets/images/char/head/terrence.png'),
        'body': require('@/assets/images/char/body/terrence.png'),
        'portrait': require('@/assets/images/char/portrait/Terrence_archer_portrait.jpg')
    },
    [CharacterEnum.NERON]: {
        'name': 'Neron',
        'completeName': 'Neron',
        'head': require('@/assets/images/comms/neron-mini.png'),
        'body': require('@/assets/images/comms/neron_chat.png')
    },
    [DEFAULT]: {
        'name': 'Hero',
        'head': require('@/assets/images/char/head/lambda_f.png'),
        'body': require('@/assets/images/char/body/lambda_f.png')
    }
}
;
