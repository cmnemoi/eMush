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
        'head': '/src/assets/images/char/head/andie.png',
        'body': '/src/assets/images/char/body/andie.png',
        'portrait': '/src/assets/images/char/portrait/andie_graham_portrait.jpg'
    },
    [CharacterEnum.CHAO]: {
        'name': 'Chao',
        'completeName': 'Wang Chao',
        'head': '/src/assets/images/char/head/chao.png',
        'body': '/src/assets/images/char/body/chao.png',
        'portrait': '/src/assets/images/char/portrait/Wang_chao_portrait.jpg'
    },
    [CharacterEnum.CHUN]: {
        'name': 'Chun',
        'completeName': 'Zhong Chun',
        'head': '/src/assets/images/char/head/chun.png',
        'body': '/src/assets/images/char/body/chun.png',
        'portrait': '/src/assets/images/char/portrait/Zhong_chun_portrait.jpg'
    },
    [CharacterEnum.DEREK]: {
        'name': 'Derek',
        'completeName': 'Derek Hogan',
        'head': '/src/assets/images/char/head/derek.png',
        'body': '/src/assets/images/char/body/derek.png',
        'portrait': '/src/assets/images/char/portrait/derek_hogan_portrait.jpg'
    },
    [CharacterEnum.ELEESHA]: {
        'name': 'Eleesha',
        'completeName': 'Eleesha Williams',
        'head': '/src/assets/images/char/head/eleesha.png',
        'body': '/src/assets/images/char/body/eleesha.png',
        'portrait': '/src/assets/images/char/portrait/Eleesha_williams_portrait.jpg'
    },
    [CharacterEnum.FINOLA]: {
        'name': 'Finola',
        'completeName': 'Finola Keegan',
        'head': '/src/assets/images/char/head/finola.png',
        'body': '/src/assets/images/char/body/finola.png',
        'portrait': '/src/assets/images/char/portrait/Finola_keegan_portrait.jpg'
    },
    [CharacterEnum.FRIEDA]: {
        'name': 'Frieda',
        'completeName': 'Frieda Bergmann',
        'head': '/src/assets/images/char/head/frieda.png',
        'body': '/src/assets/images/char/body/frieda.png',
        'portrait': '/src/assets/images/char/portrait/Frieda_bergmann_portrait.jpg'
    },
    [CharacterEnum.GIOELE]: {
        'name': 'Gioele',
        'completeName': 'Gioele Rinaldo',
        'head': '/src/assets/images/char/head/gioele.png',
        'body': '/src/assets/images/char/body/gioele.png',
        'portrait': '/src/assets/images/char/portrait/Gioele_rinaldo_portrait.jpg'
    },
    [CharacterEnum.HUA]: {
        'name': 'Hua',
        'completeName': 'Jiang Hua',
        'head': '/src/assets/images/char/head/hua.png',
        'body': '/src/assets/images/char/body/hua.png',
        'portrait': '/src/assets/images/char/portrait/Jiang_hua_portrait.jpg'
    },
    [CharacterEnum.IAN]: {
        'name': 'Ian',
        'completeName': 'Ian Soulton',
        'head': '/src/assets/images/char/head/ian.png',
        'body': '/src/assets/images/char/body/ian.png',
        'portrait': '/src/assets/images/char/portrait/Ian_soulton_portrait.jpg'
    },
    [CharacterEnum.JANICE]: {
        'name': 'Janice',
        'completeName': 'Janice Kent',
        'head': '/src/assets/images/char/head/janice.png',
        'body': '/src/assets/images/char/body/janice.png',
        'portrait': '/src/assets/images/char/portrait/Janice_kent_portrait.jpg'
    },
    [CharacterEnum.JIN_SU]: {
        'name': 'Jin Su',
        'completeName': 'Kim Jin Su',
        'head': '/src/assets/images/char/head/jin_su.png',
        'body': '/src/assets/images/char/body/jin_su.png',
        'portrait': '/src/assets/images/char/portrait/Kim_jin_su_portrait.jpg'
    },
    [CharacterEnum.KUAN_TI]: {
        'name': 'Kuan Ti',
        'completeName': 'Lai Kuan Ti',
        'head': '/src/assets/images/char/head/kuan_ti.png',
        'body': '/src/assets/images/char/body/kuan_ti.png',
        'portrait': '/src/assets/images/char/portrait/Lai_kuan_ti_portrait.jpg'
    },
    [CharacterEnum.PAOLA]: {
        'name': 'Paola',
        'completeName': 'Paola Rinaldo',
        'head': '/src/assets/images/char/head/paola.png',
        'body': '/src/assets/images/char/body/paola.png',
        'portrait': '/src/assets/images/char/portrait/Paola_rinaldo_portrait.jpg'
    },
    [CharacterEnum.RALUCA]: {
        'name': 'Raluca',
        'completeName': 'Raluca Tomescu',
        'head': '/src/assets/images/char/head/raluca.png',
        'body': '/src/assets/images/char/body/raluca.png',
        'portrait': '/src/assets/images/char/portrait/Raluca_tomescu_portrait.jpg'
    },
    [CharacterEnum.ROLAND]: {
        'name': 'Roland',
        'completeName': 'Roland Zuccali',
        'head': '/src/assets/images/char/head/roland.png',
        'body': '/src/assets/images/char/body/roland.png',
        'portrait': '/src/assets/images/char/portrait/Roland_zuccali_portrait.jpg'
    },
    [CharacterEnum.STEPHEN]: {
        'name': 'Stephen',
        'completeName': 'Stephen Seagull',
        'head': '/src/assets/images/char/head/stephen.png',
        'body': '/src/assets/images/char/body/stephen.png',
        'portrait': '/src/assets/images/char/portrait/Stephen_seagull_portrait.jpg'
    },
    [CharacterEnum.TERRENCE]: {
        'name': 'Terrence',
        'completeName': 'Terrence Archer',
        'head': '/src/assets/images/char/head/terrence.png',
        'body': '/src/assets/images/char/body/terrence.png',
        'portrait': '/src/assets/images/char/portrait/Terrence_archer_portrait.jpg'
    },
    [CharacterEnum.NERON]: {
        'name': 'Neron',
        'completeName': 'Neron',
        'head': '/src/assets/images/comms/neron-mini.png',
        'body': '/src/assets/images/comms/neron_chat.png',
    },
    [DEFAULT]: {
        'name': 'Hero',
        'head': '/src/assets/images/char/head/lambda_f.png',
        'body': '/src/assets/images/char/body/lambda_f.png',
    }
}
;
