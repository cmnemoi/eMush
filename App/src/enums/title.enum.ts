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
        'image': require('@/assets/images/title_commander.png')
    },
    [TitleEnum.COM_MANAGER]: {
        'image': require('@/assets/images/title_com_manager.png')
    },
    [TitleEnum.NERON_MANAGER]: {
        'image': require('@/assets/images/title_neron_manager.png')
    },
    [DEFAULT]: {
        'image': require('@/assets/images/title_commander.png')
    }
}
;
