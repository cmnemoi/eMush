import { getImgUrl } from "@/utils/getImgUrl";

const DEFAULT = "default";

export enum TitleEnum {
    COMMANDER = "commander",
    COM_MANAGER = "com_manager",
    NERON_MANAGER = "neron_manager",
}

export interface TitleInfos {
    image: string,
};

export const titleEnum : {[index: string]: TitleInfos}  = {
    [TitleEnum.COMMANDER]: {
        'image': getImgUrl('title_commander.png'),
    },
    [TitleEnum.COM_MANAGER]: {
        'image': getImgUrl('title_com_manager.png'),
    },
    [TitleEnum.NERON_MANAGER]: {
        'image': getImgUrl('title_neron_manager.png'),
    },
    [DEFAULT]: {
        'image': getImgUrl('title_commander.png'),
    }
}
;
