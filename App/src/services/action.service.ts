import ApiService from "@/services/api.service";
import { Door } from "@/entities/Door";
import { Item } from "@/entities/Item";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";
import { Action } from "@/entities/Action";
import { AxiosResponse } from "axios";
import urlJoin from "url-join";
import store from "@/store";

// @ts-ignore
const PLAYER_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "player");
// @ts-ignore
const ACTION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "actions");

const ActionService = {
    executeTargetAction(target: Item | Equipment | Player | null, action: Action): Promise<AxiosResponse> {
        const isLoading = store.getters["player/isLoading"];
        if (isLoading) {
            throw new Error('player is still loading');
        }

        const currentPlayer = store.getters["player/player"];
        return ApiService.post(urlJoin(PLAYER_ENDPOINT, String(currentPlayer.id),'action'), {
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
    },
    loadAction: async(actionId: number): Promise<Action | null> => {
        store.dispatch('action/setLoading', { loading: true });
        const actionData = await ApiService.get(ACTION_ENDPOINT + '/' + actionId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('action/setLoading', { loading: false })));
        store.dispatch('action/setLoading', { loading: false });
        let action = null;
        if (actionData.data) {
            action = (new Action()).load(actionData.data);
        }

        return action;
    },

};
export default ActionService;
