import { getImgUrl } from "@/utils/getImgUrl";

export enum StatusRoomNameEnum {
    FIRE = "fire",
    MUSH_TRAPPED = "mush_trapped",
}

export const statusRoomEnum: {[index: string]: any} = {
    [StatusRoomNameEnum.FIRE]: {
        'icon': getImgUrl('alerts/fire.png')
    },
    [StatusRoomNameEnum.MUSH_TRAPPED]: {
        'icon': getImgUrl('status/mush.png')
    }
};
