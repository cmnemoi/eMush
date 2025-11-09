import { getImgUrl } from "@/utils/getImgUrl";

export enum EmoteCharacterEnum {
    ANDIE = 'andie',
    CHAO = 'chao',
    CHUN = 'chun',
    DEREK = 'derek',
    ELEESHA = 'eleesha',
    FINOLA = 'finola',
    FRIEDA = 'frieda',
    GIOELE = 'gioele',
    HUA = 'hua',
    IAN = 'ian',
    JANICE = 'janice',
    JIN_SU = 'jin_su',
    KUAN_TI = 'kuan_ti',
    MUSH = 'mush',
    PAOLA = 'paola',
    RALUCA = 'raluca',
    ROLAND = 'roland',
    STEPHEN = 'stephen',
    TERRENCE = 'terrence',
    NERON = 'neron',
    FEMALE_ADMIN = "female_admin",
    MALE_ADMIN = "male_admin",
}

export const EmoteCharacterAliases: { [index: string]: string } = {
    ['kuanti']: EmoteCharacterEnum.KUAN_TI,
    ['kuan']: EmoteCharacterEnum.KUAN_TI,
    ['jinsu']: EmoteCharacterEnum.JIN_SU,
    ['jin']: EmoteCharacterEnum.JIN_SU,
    ['female']: EmoteCharacterEnum.FEMALE_ADMIN,
    ['male']: EmoteCharacterEnum.MALE_ADMIN
};

export const EmoteCharacterIcons: { [index: string]: string } = {
    [EmoteCharacterEnum.ANDIE]: getImgUrl('char/head/andie.png'),
    [EmoteCharacterEnum.CHAO]: getImgUrl('char/head/chao.png'),
    [EmoteCharacterEnum.CHUN]: getImgUrl('char/head/chun.png'),
    [EmoteCharacterEnum.DEREK]: getImgUrl('char/head/derek.png'),
    [EmoteCharacterEnum.ELEESHA]: getImgUrl('char/head/eleesha.png'),
    [EmoteCharacterEnum.FINOLA]: getImgUrl('char/head/finola.png'),
    [EmoteCharacterEnum.FRIEDA]: getImgUrl('char/head/frieda.png'),
    [EmoteCharacterEnum.GIOELE]: getImgUrl('char/head/gioele.png'),
    [EmoteCharacterEnum.HUA]: getImgUrl('char/head/hua.png'),
    [EmoteCharacterEnum.IAN]: getImgUrl('char/head/ian.png'),
    [EmoteCharacterEnum.JANICE]: getImgUrl('char/head/janice.png'),
    [EmoteCharacterEnum.JIN_SU]: getImgUrl('char/head/jin_su.png'),
    [EmoteCharacterEnum.KUAN_TI]: getImgUrl('char/head/kuan_ti.png'),
    [EmoteCharacterEnum.MUSH]: getImgUrl('char/head/mush.png'),
    [EmoteCharacterEnum.PAOLA]: getImgUrl('char/head/paola.png'),
    [EmoteCharacterEnum.RALUCA]: getImgUrl('char/head/raluca.png'),
    [EmoteCharacterEnum.ROLAND]: getImgUrl('char/head/roland.png'),
    [EmoteCharacterEnum.STEPHEN]: getImgUrl('char/head/stephen.png'),
    [EmoteCharacterEnum.TERRENCE]: getImgUrl('char/head/terrence.png'),
    [EmoteCharacterEnum.NERON]: getImgUrl('ui_icons/action_points/pa_core.png'),
    [EmoteCharacterEnum.FEMALE_ADMIN]: getImgUrl('char/head/lambda_f.png'),
    [EmoteCharacterEnum.MALE_ADMIN]: getImgUrl('char/head/lambda_m.png')
};
