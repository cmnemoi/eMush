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
        'image': 'src/assets/images/title_commander.png',
    },
    [TitleEnum.COM_MANAGER]: {
        'image': 'src/assets/images/title_com_manager.png',
    },
    [TitleEnum.NERON_MANAGER]: {
        'image': 'src/assets/images/title_neron_manager.png',
    },
    [DEFAULT]: {
        'image': 'src/assets/images/title_commander.png',
    }
}
;
