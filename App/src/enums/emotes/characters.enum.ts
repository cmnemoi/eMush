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
    MUSH = 'berserk',
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
    ['female']: EmoteCharacterEnum.FEMALE_ADMIN,
    ['jin']: EmoteCharacterEnum.JIN_SU,
    ['jinsu']: EmoteCharacterEnum.JIN_SU,
    ['kuan']: EmoteCharacterEnum.KUAN_TI,
    ['kuanti']: EmoteCharacterEnum.KUAN_TI,
    ['male']: EmoteCharacterEnum.MALE_ADMIN
};

export const EmoteCharacterIcons: {[index: string]: {img: string; max_height: string;}}= {
    [EmoteCharacterEnum.ANDIE]: { img: getImgUrl('char/head/andie.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.CHAO]: { img: getImgUrl('char/head/chao.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.CHUN]: { img: getImgUrl('char/head/chun.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.DEREK]: { img: getImgUrl('char/head/derek.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.ELEESHA]: { img: getImgUrl('char/head/eleesha.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.FINOLA]: { img: getImgUrl('char/head/finola.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.FRIEDA]: { img: getImgUrl('char/head/frieda.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.GIOELE]: { img: getImgUrl('char/head/gioele.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.HUA]: { img: getImgUrl('char/head/hua.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.IAN]: { img: getImgUrl('char/head/ian.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.JANICE]: { img: getImgUrl('char/head/janice.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.JIN_SU]: { img: getImgUrl('char/head/jin_su.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.KUAN_TI]: { img: getImgUrl('char/head/kuan_ti.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.MUSH]: { img: getImgUrl('char/head/mush.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.PAOLA]: { img: getImgUrl('char/head/paola.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.RALUCA]: { img: getImgUrl('char/head/raluca.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.ROLAND]: { img: getImgUrl('char/head/roland.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.STEPHEN]: { img: getImgUrl('char/head/stephen.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.TERRENCE]: { img: getImgUrl('char/head/terrence.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.NERON]: { img: getImgUrl('ui_icons/action_points/pa_core.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.FEMALE_ADMIN]: { img: getImgUrl('char/head/lambda_f.png'), max_height: "1.2em" },
    [EmoteCharacterEnum.MALE_ADMIN]: { img: getImgUrl('char/head/lambda_m.png'), max_height: "1.2em" }
};
