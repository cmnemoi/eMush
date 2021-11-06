import ApiService from "@/services/api.service";
import { Door } from "@/entities/Door";
import { Item } from "@/entities/Item";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";
import { Action } from "@/entities/Action";
import { AxiosResponse } from "axios";
import urlJoin from "url-join";

// @ts-ignore
const ACTION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "action");

const ActionService = {
    executeTargetAction(target: Door | Item | Equipment | Player | null, action: Action): Promise<AxiosResponse> {
        return ApiService.post(ACTION_ENDPOINT, {
            action: action.id,
            params: buildParams()
        });

        function buildParams(): Record<string, unknown> | undefined {
            if (target instanceof Door) {
                return { door: target.id };
            } else if (target instanceof Item) {
                return { item: target.id };
            } else if (target instanceof Equipment) {
                return { equipment: target.id };
            } else if (target instanceof Player) {
                return { player: target.id };
            }
        }
    }
};
export default ActionService;
