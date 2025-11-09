import { getImgUrl } from "@/utils/getImgUrl";

export enum EmoteEternalTwinEnum {
    ETERNALTWIN = 'et_eternal_twin',
    CALIM = 'et_calim',
    XMAS = 'et_xmas',
    SOCKS = 'et_mr_socks',
    DEVILHORNS = 'et_metal',
    IGOR = 'et_snowman',
    MONEY = 'et_money',
    PIOUZ = 'et_pink_bird',
    AGREE = 'et_plus_1',
    RAINBOW = 'et_rainbow',
    NEWGIF = 'et_anim_new',
    READY = 'et_ready',
    INGAME = 'et_in_game',
}

export const EmoteEternalTwinAliases: { [index: string]: string } = {
    ['ready']: EmoteEternalTwinEnum.READY,
    ['ingame']: EmoteEternalTwinEnum.INGAME
};

export const EmoteEternalTwinIcons: {[index: string]: {img: string; max_height: string;}}= {
    [EmoteEternalTwinEnum.ETERNALTWIN]: { img: getImgUrl('emotes/eternaltwin.png'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.CALIM]: { img: getImgUrl('emotes/calim.gif'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.XMAS]: { img: getImgUrl('emotes/calimchristmas.gif'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.SOCKS]: { img: getImgUrl('emotes/h_socks.gif'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.DEVILHORNS]: { img: getImgUrl('emotes/devilhorns.png'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.IGOR]: { img: getImgUrl('emotes/igor.png'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.MONEY]: { img: getImgUrl('emotes/money.png'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.PIOUZ]: { img: getImgUrl('emotes/piou.gif'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.AGREE]: { img: getImgUrl('emotes/Plus1.png'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.RAINBOW]: { img: getImgUrl('emotes/rainbow.gif'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.NEWGIF]: { img: getImgUrl('ui_icons/anim_new.gif'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.READY]: { img: getImgUrl('ready.png'), max_height: "1.2em" },
    [EmoteEternalTwinEnum.INGAME]: { img: getImgUrl('in_game.png'), max_height: "1.2em" }

};
