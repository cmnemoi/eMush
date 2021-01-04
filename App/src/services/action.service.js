import ApiService from "@/services/api.service";
import {Item} from "@/entities/Item";
import {Player} from "@/entities/Player";
import {Equipment} from "@/entities/Equipment";

const ACTION_ENDPOINT = process.env.VUE_APP_API_URL+'action'

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

    executeTargetAction: (target, action) => {
        let param = null;
        if (target instanceof Item) {
            param = 'item'
        } else if (target instanceof Equipment) {
            param = 'equipment'
        } else if (target instanceof Player) {
            param = 'player'
        }

        let params = {}
        if (param !== null) {
            params = {
                [param]: target.id
            }
        }

        let data = {
                "action": action.id,
                "params": params
            }
        ;

        return ApiService.post(ACTION_ENDPOINT, data);
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
}
export default ActionService