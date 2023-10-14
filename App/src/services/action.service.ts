import ApiService from "@/services/api.service";
import { Door } from "@/entities/Door";
import { Item } from "@/entities/Item";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";
import { Action } from "@/entities/Action";
import { AxiosResponse } from "axios";
import urlJoin from "url-join";
import store from "@/store";
import { Hunter } from "@/entities/Hunter";
import { Terminal } from "@/entities/Terminal";

// @ts-ignore
const PLAYER_ENDPOINT = urlJoin(import.meta.env.VITE_API_URL, "player");
// @ts-ignore
const ACTION_ENDPOINT = urlJoin(import.meta.env.VITE_API_URL, "actions");

const ActionService = {
    executeTargetAction(target: Item | Equipment | Player | Hunter | Terminal | null, action: Action, otherParams: object = {}): Promise<AxiosResponse> {
        const currentPlayer = store.getters["player/player"];
        return ApiService.post(urlJoin(PLAYER_ENDPOINT, String(currentPlayer.id),'action'), {
            action: action.id,
            params: {
                target: buildTarget(),
                ...otherParams
            }
        });

        function buildTarget(): Record<string, unknown> | undefined | null {
            if (target instanceof Door) {
                return { door: target.id };
            } else if (target instanceof Item) {
                return { item: target.id };
            } else if (target instanceof Equipment) {
                return { equipment: target.id };
            } else if (target instanceof Player) {
                return { player: target.id };
            } else if (target instanceof Hunter) {
                return { hunter: target.id };
            } else if (target instanceof Terminal) {
                return { terminal: target.id };
            } else {
                return null;
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
