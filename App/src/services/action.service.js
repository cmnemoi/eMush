import ApiService from "@/services/api.service";
import { Door } from "@/entities/Door";
import { Item } from "@/entities/Item";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";

const ACTION_ENDPOINT = process.env.VUE_APP_API_URL+'action';

const ActionService = {
    /**
     * @param item
     * @param action
     */
    executeItemAction: (item, action) => {
        const data = {
            "action": action.id,
            "params" : {
                "item": item.id
            }
        };
        return ApiService.post(ACTION_ENDPOINT, data);
    },

    executeTargetAction(target, action) {
        return ApiService.post(ACTION_ENDPOINT, {
            action: action.id,
            params: buildParams()
        });

        function buildParams() {
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
    },

    executeDoorAction: (door, action) => {
        const data = {
            "action": action.id,
            "params" : {
                "door": door.id
            }
        };
        return ApiService.post(ACTION_ENDPOINT, data);
    }
};
export default ActionService;
