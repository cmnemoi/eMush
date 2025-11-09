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

export const EmoteEternalTwinIcons: { [index: string]: string } = {
    [EmoteEternalTwinEnum.ETERNALTWIN]: getImgUrl('emotes/eternaltwin.png'),
    [EmoteEternalTwinEnum.CALIM]: getImgUrl('emotes/calim.gif'),
    [EmoteEternalTwinEnum.XMAS]: getImgUrl('emotes/calimchristmas.gif'),
    [EmoteEternalTwinEnum.SOCKS]: getImgUrl('emotes/h_socks.gif'),
    [EmoteEternalTwinEnum.DEVILHORNS]: getImgUrl('emotes/devilhorns.png'),
    [EmoteEternalTwinEnum.IGOR]: getImgUrl('emotes/igor.png'),
    [EmoteEternalTwinEnum.MONEY]: getImgUrl('emotes/money.png'),
    [EmoteEternalTwinEnum.PIOUZ]: getImgUrl('emotes/piou.gif'),
    [EmoteEternalTwinEnum.AGREE]: getImgUrl('emotes/Plus1.png'),
    [EmoteEternalTwinEnum.RAINBOW]: getImgUrl('emotes/rainbow.gif'),
    [EmoteEternalTwinEnum.NEWGIF]: getImgUrl('ui_icons/anim_new.gif'),
    [EmoteEternalTwinEnum.READY]: getImgUrl('ready.png'),
    [EmoteEternalTwinEnum.INGAME]: getImgUrl('in_game.png')

};
