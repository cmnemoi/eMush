import ApiService from "@/services/api.service";

const ACTION_ENDPOINT = process.env.VUE_APP_API_URL+'action'

const ActionService = {
    /**
     * @param item
     * @param action
     */
    executeItemAction: (item, action) => {
        const data = {
            "action": action.key,
            "params" : {
                "item": item.id
            }
        };
        return ApiService.post(ACTION_ENDPOINT, data);
    },

    executeDoorAction: (door, action) => {
        const data = {
            "action": action.key,
            "params" : {
                "door": door.id
            }
        };
        return ApiService.post(ACTION_ENDPOINT, data);
    }
}
export default ActionService