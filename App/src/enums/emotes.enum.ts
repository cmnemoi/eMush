import { getImgUrl } from "@/utils/getImgUrl";

export enum EmoteEnum{
    CALIM = 'calim',
    XMAS = 'xmas',
    SOCKS = 'mr_socks',
    DEVILHORNS = 'metal',
    IGOR = 'snowman',
    MONEY = 'money',
    PIOUZ = 'pink_bird',
    AGREE = 'plus_1',
    RAINBOW = 'rainbow',
    SOL = 'sol',
    EDEN = 'eden',
    EDENMUSH = 'eden_mush',
    NEWGIF = 'anim_new',
    BOOK = 'book',
    COFFEE = 'coffee',
    EXTINGUISHER = 'extinguisher',
    GRENADE = 'grenade',
    RATION = 'ration',
    SOLDIER = 'soldier',
    SURVIVAL = 'survival',
    READY = 'ready',
    INGAME = 'in_game',
}

export const EmoteIcons: {[index: string]: string} = {
    [EmoteEnum.CALIM]: getImgUrl('emotes/calim.gif'),
    [EmoteEnum.XMAS]: getImgUrl('emotes/calimchristmas.gif'),
    [EmoteEnum.SOCKS]: getImgUrl('emotes/h_socks.gif'),
    [EmoteEnum.DEVILHORNS]: getImgUrl('emotes/devilhorns.png'),
    [EmoteEnum.IGOR]: getImgUrl('emotes/igor.png'),
    [EmoteEnum.MONEY]: getImgUrl('emotes/money.png'),
    [EmoteEnum.PIOUZ]: getImgUrl('emotes/piou.gif'),
    [EmoteEnum.AGREE]: getImgUrl('emotes/Plus1.png'),
    [EmoteEnum.RAINBOW]: getImgUrl('emotes/rainbow.gif'),
    [EmoteEnum.SOL]: getImgUrl('ui_icons/sol.png'),
    [EmoteEnum.EDEN]: getImgUrl('ui_icons/eden1.png'),
    [EmoteEnum.EDENMUSH]: getImgUrl('ui_icons/eden2.png'),
    [EmoteEnum.NEWGIF]: getImgUrl('ui_icons/anim_new.gif'),
    [EmoteEnum.BOOK]: getImgUrl('ui_icons/book.png'),
    [EmoteEnum.COFFEE]: getImgUrl('ui_icons/coffee.png'),
    [EmoteEnum.EXTINGUISHER]: getImgUrl('ui_icons/extinguisher.png'),
    [EmoteEnum.GRENADE]: getImgUrl('ui_icons/grenade.png'),
    [EmoteEnum.RATION]: getImgUrl('ui_icons/ration.png'),
    [EmoteEnum.SOLDIER]: getImgUrl('ui_icons/soldier.png'),
    [EmoteEnum.SURVIVAL]: getImgUrl('ui_icons/survival.png'),
    [EmoteEnum.READY]: getImgUrl('ready.png'),
    [EmoteEnum.INGAME]: getImgUrl('in_game.png')

};

// In the long run this should be hooked into EternalTwin systems, for cross-game fetching of emotes.
