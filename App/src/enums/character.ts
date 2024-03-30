import { getImgUrl } from "@/utils/getImgUrl";

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
        'head': getImgUrl('char/head/andie.png'),
        'body': getImgUrl('char/body/andie.png'),
        'portrait': getImgUrl('char/portrait/andie_graham_portrait.jpg')
    },
    [CharacterEnum.CHAO]: {
        'name': 'Chao',
        'completeName': 'Wang Chao',
        'head': getImgUrl('char/head/chao.png'),
        'body': getImgUrl('char/body/chao.png'),
        'portrait': getImgUrl('char/portrait/Wang_chao_portrait.jpg')
    },
    [CharacterEnum.CHUN]: {
        'name': 'Chun',
        'completeName': 'Zhong Chun',
        'head': getImgUrl('char/head/chun.png'),
        'body': getImgUrl('char/body/chun.png'),
        'portrait': getImgUrl('char/portrait/Zhong_chun_portrait.jpg')
    },
    [CharacterEnum.DEREK]: {
        'name': 'Derek',
        'completeName': 'Derek Hogan',
        'head': getImgUrl('char/head/derek.png'),
        'body': getImgUrl('char/body/derek.png'),
        'portrait': getImgUrl('char/portrait/derek_hogan_portrait.jpg')
    },
    [CharacterEnum.ELEESHA]: {
        'name': 'Eleesha',
        'completeName': 'Eleesha Williams',
        'head': getImgUrl('char/head/eleesha.png'),
        'body': getImgUrl('char/body/eleesha.png'),
        'portrait': getImgUrl('char/portrait/Eleesha_williams_portrait.jpg')
    },
    [CharacterEnum.FINOLA]: {
        'name': 'Finola',
        'completeName': 'Finola Keegan',
        'head': getImgUrl('char/head/finola.png'),
        'body': getImgUrl('char/body/finola.png'),
        'portrait': getImgUrl('char/portrait/Finola_keegan_portrait.jpg')
    },
    [CharacterEnum.FRIEDA]: {
        'name': 'Frieda',
        'completeName': 'Frieda Bergmann',
        'head': getImgUrl('char/head/frieda.png'),
        'body': getImgUrl('char/body/frieda.png'),
        'portrait': getImgUrl('char/portrait/Frieda_bergmann_portrait.jpg')
    },
    [CharacterEnum.GIOELE]: {
        'name': 'Gioele',
        'completeName': 'Gioele Rinaldo',
        'head': getImgUrl('char/head/gioele.png'),
        'body': getImgUrl('char/body/gioele.png'),
        'portrait': getImgUrl('char/portrait/Gioele_rinaldo_portrait.jpg')
    },
    [CharacterEnum.HUA]: {
        'name': 'Hua',
        'completeName': 'Jiang Hua',
        'head': getImgUrl('char/head/hua.png'),
        'body': getImgUrl('char/body/hua.png'),
        'portrait': getImgUrl('char/portrait/Jiang_hua_portrait.jpg')
    },
    [CharacterEnum.IAN]: {
        'name': 'Ian',
        'completeName': 'Ian Soulton',
        'head': getImgUrl('char/head/ian.png'),
        'body': getImgUrl('char/body/ian.png'),
        'portrait': getImgUrl('char/portrait/Ian_soulton_portrait.jpg')
    },
    [CharacterEnum.JANICE]: {
        'name': 'Janice',
        'completeName': 'Janice Kent',
        'head': getImgUrl('char/head/janice.png'),
        'body': getImgUrl('char/body/janice.png'),
        'portrait': getImgUrl('char/portrait/Janice_kent_portrait.jpg')
    },
    [CharacterEnum.JIN_SU]: {
        'name': 'Jin Su',
        'completeName': 'Kim Jin Su',
        'head': getImgUrl('char/head/jin_su.png'),
        'body': getImgUrl('char/body/jin_su.png'),
        'portrait': getImgUrl('char/portrait/Kim_jin_su_portrait.jpg')
    },
    [CharacterEnum.KUAN_TI]: {
        'name': 'Kuan Ti',
        'completeName': 'Lai Kuan Ti',
        'head': getImgUrl('char/head/kuan_ti.png'),
        'body': getImgUrl('char/body/kuan_ti.png'),
        'portrait': getImgUrl('char/portrait/Lai_kuan_ti_portrait.jpg')
    },
    [CharacterEnum.PAOLA]: {
        'name': 'Paola',
        'completeName': 'Paola Rinaldo',
        'head': getImgUrl('char/head/paola.png'),
        'body': getImgUrl('char/body/paola.png'),
        'portrait': getImgUrl('char/portrait/Paola_rinaldo_portrait.jpg')
    },
    [CharacterEnum.RALUCA]: {
        'name': 'Raluca',
        'completeName': 'Raluca Tomescu',
        'head': getImgUrl('char/head/raluca.png'),
        'body': getImgUrl('char/body/raluca.png'),
        'portrait': getImgUrl('char/portrait/Raluca_tomescu_portrait.jpg')
    },
    [CharacterEnum.ROLAND]: {
        'name': 'Roland',
        'completeName': 'Roland Zuccali',
        'head': getImgUrl('char/head/roland.png'),
        'body': getImgUrl('char/body/roland.png'),
        'portrait': getImgUrl('char/portrait/Roland_zuccali_portrait.jpg')
    },
    [CharacterEnum.STEPHEN]: {
        'name': 'Stephen',
        'completeName': 'Stephen Seagull',
        'head': getImgUrl('char/head/stephen.png'),
        'body': getImgUrl('char/body/stephen.png'),
        'portrait': getImgUrl('char/portrait/Stephen_seagull_portrait.jpg')
    },
    [CharacterEnum.TERRENCE]: {
        'name': 'Terrence',
        'completeName': 'Terrence Archer',
        'head': getImgUrl('char/head/terrence.png'),
        'body': getImgUrl('char/body/terrence.png'),
        'portrait': getImgUrl('char/portrait/Terrence_archer_portrait.jpg')
    },
    [CharacterEnum.NERON]: {
        'name': 'Neron',
        'completeName': 'Neron',
        'head': getImgUrl('comms/neron-mini.png'),
        'body': getImgUrl('comms/neron_chat.png'),
    },
    [DEFAULT]: {
        'name': 'Hero',
        'head': getImgUrl('char/head/lambda_f.png'),
        'body': getImgUrl('char/body/lambda_f.png'),
    }
}
;
