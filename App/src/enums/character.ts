import { getAssetUrl } from "@/utils/getAssetUrl";

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
        'head': getAssetUrl('char/head/andie.png'),
        'body': getAssetUrl('char/body/andie.png'),
        'portrait': getAssetUrl('char/portrait/andie_graham_portrait.jpg')
    },
    [CharacterEnum.CHAO]: {
        'name': 'Chao',
        'completeName': 'Wang Chao',
        'head': getAssetUrl('char/head/chao.png'),
        'body': getAssetUrl('char/body/chao.png'),
        'portrait': getAssetUrl('char/portrait/Wang_chao_portrait.jpg')
    },
    [CharacterEnum.CHUN]: {
        'name': 'Chun',
        'completeName': 'Zhong Chun',
        'head': getAssetUrl('char/head/chun.png'),
        'body': getAssetUrl('char/body/chun.png'),
        'portrait': getAssetUrl('char/portrait/Zhong_chun_portrait.jpg')
    },
    [CharacterEnum.DEREK]: {
        'name': 'Derek',
        'completeName': 'Derek Hogan',
        'head': getAssetUrl('char/head/derek.png'),
        'body': getAssetUrl('char/body/derek.png'),
        'portrait': getAssetUrl('char/portrait/derek_hogan_portrait.jpg')
    },
    [CharacterEnum.ELEESHA]: {
        'name': 'Eleesha',
        'completeName': 'Eleesha Williams',
        'head': getAssetUrl('char/head/eleesha.png'),
        'body': getAssetUrl('char/body/eleesha.png'),
        'portrait': getAssetUrl('char/portrait/Eleesha_williams_portrait.jpg')
    },
    [CharacterEnum.FINOLA]: {
        'name': 'Finola',
        'completeName': 'Finola Keegan',
        'head': getAssetUrl('char/head/finola.png'),
        'body': getAssetUrl('char/body/finola.png'),
        'portrait': getAssetUrl('char/portrait/Finola_keegan_portrait.jpg')
    },
    [CharacterEnum.FRIEDA]: {
        'name': 'Frieda',
        'completeName': 'Frieda Bergmann',
        'head': getAssetUrl('char/head/frieda.png'),
        'body': getAssetUrl('char/body/frieda.png'),
        'portrait': getAssetUrl('char/portrait/Frieda_bergmann_portrait.jpg')
    },
    [CharacterEnum.GIOELE]: {
        'name': 'Gioele',
        'completeName': 'Gioele Rinaldo',
        'head': getAssetUrl('char/head/gioele.png'),
        'body': getAssetUrl('char/body/gioele.png'),
        'portrait': getAssetUrl('char/portrait/Gioele_rinaldo_portrait.jpg')
    },
    [CharacterEnum.HUA]: {
        'name': 'Hua',
        'completeName': 'Jiang Hua',
        'head': getAssetUrl('char/head/hua.png'),
        'body': getAssetUrl('char/body/hua.png'),
        'portrait': getAssetUrl('char/portrait/Jiang_hua_portrait.jpg')
    },
    [CharacterEnum.IAN]: {
        'name': 'Ian',
        'completeName': 'Ian Soulton',
        'head': getAssetUrl('char/head/ian.png'),
        'body': getAssetUrl('char/body/ian.png'),
        'portrait': getAssetUrl('char/portrait/Ian_soulton_portrait.jpg')
    },
    [CharacterEnum.JANICE]: {
        'name': 'Janice',
        'completeName': 'Janice Kent',
        'head': getAssetUrl('char/head/janice.png'),
        'body': getAssetUrl('char/body/janice.png'),
        'portrait': getAssetUrl('char/portrait/Janice_kent_portrait.jpg')
    },
    [CharacterEnum.JIN_SU]: {
        'name': 'Jin Su',
        'completeName': 'Kim Jin Su',
        'head': getAssetUrl('char/head/jin_su.png'),
        'body': getAssetUrl('char/body/jin_su.png'),
        'portrait': getAssetUrl('char/portrait/Kim_jin_su_portrait.jpg')
    },
    [CharacterEnum.KUAN_TI]: {
        'name': 'Kuan Ti',
        'completeName': 'Lai Kuan Ti',
        'head': getAssetUrl('char/head/kuan_ti.png'),
        'body': getAssetUrl('char/body/kuan_ti.png'),
        'portrait': getAssetUrl('char/portrait/Lai_kuan_ti_portrait.jpg')
    },
    [CharacterEnum.PAOLA]: {
        'name': 'Paola',
        'completeName': 'Paola Rinaldo',
        'head': getAssetUrl('char/head/paola.png'),
        'body': getAssetUrl('char/body/paola.png'),
        'portrait': getAssetUrl('char/portrait/Paola_rinaldo_portrait.jpg')
    },
    [CharacterEnum.RALUCA]: {
        'name': 'Raluca',
        'completeName': 'Raluca Tomescu',
        'head': getAssetUrl('char/head/raluca.png'),
        'body': getAssetUrl('char/body/raluca.png'),
        'portrait': getAssetUrl('char/portrait/Raluca_tomescu_portrait.jpg')
    },
    [CharacterEnum.ROLAND]: {
        'name': 'Roland',
        'completeName': 'Roland Zuccali',
        'head': getAssetUrl('char/head/roland.png'),
        'body': getAssetUrl('char/body/roland.png'),
        'portrait': getAssetUrl('char/portrait/Roland_zuccali_portrait.jpg')
    },
    [CharacterEnum.STEPHEN]: {
        'name': 'Stephen',
        'completeName': 'Stephen Seagull',
        'head': getAssetUrl('char/head/stephen.png'),
        'body': getAssetUrl('char/body/stephen.png'),
        'portrait': getAssetUrl('char/portrait/Stephen_seagull_portrait.jpg')
    },
    [CharacterEnum.TERRENCE]: {
        'name': 'Terrence',
        'completeName': 'Terrence Archer',
        'head': getAssetUrl('char/head/terrence.png'),
        'body': getAssetUrl('char/body/terrence.png'),
        'portrait': getAssetUrl('char/portrait/Terrence_archer_portrait.jpg')
    },
    [CharacterEnum.NERON]: {
        'name': 'Neron',
        'completeName': 'Neron',
        'head': getAssetUrl('comms/neron-mini.png'),
        'body': getAssetUrl('comms/neron_chat.png'),
    },
    [DEFAULT]: {
        'name': 'Hero',
        'head': getAssetUrl('char/head/lambda_f.png'),
        'body': getAssetUrl('char/body/lambda_f.png'),
    }
}
;
