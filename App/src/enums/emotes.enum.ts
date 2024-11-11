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
    RAINBOW = 'rainbow'
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
    [EmoteEnum.RAINBOW]: getImgUrl('emotes/rainbow.gif')

};

// In the long run this should be hooked into EternalTwin systems, for cross-game fetching of emotes.
