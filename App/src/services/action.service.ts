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
import { Planet } from "@/entities/Planet";
import { Project } from "@/entities/Project";

// @ts-ignore
const PLAYER_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "player");
// @ts-ignore
const ACTION_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "actions");

const EquipmentClassName = "Mush\\Equipment\\Entity\\GameEquipment";
const PlayerClassName = "Mush\\Player\\Entity\\Player";
const HunterClassName = "Mush\\Hunter\\Entity\\Hunter";
const PlanetClassName = "Mush\\Exploration\\Entity\\Planet";
const ProjectClassName = "Mush\\Project\\Entity\\Project";

const ActionService = {
    executeTargetAction(
        target: Item | Equipment | Player | Hunter | Terminal | Planet | Project | null,
        action: Action,
        otherParams: object = {}
    ): Promise<AxiosResponse> {
        const currentPlayer = store.getters["player/player"];

        return ApiService.post(urlJoin(PLAYER_ENDPOINT, String(currentPlayer.id),'action'), {
            action: action.id,
            params: {
                target: buildTarget(),
                actionProvider: buildActionProvider(),
                ...otherParams
            }
        });

        function buildTarget(): Record<string, unknown> | undefined | null {
            if (
                target instanceof Door
                || target instanceof Item
                || target instanceof Equipment
                || target instanceof Terminal
            ) {
                return { className: EquipmentClassName, id: target.id };
            } else if (target instanceof Player) {
                return { className: PlayerClassName, id: target.id };
            } else if (target instanceof Hunter) {
                return { className: HunterClassName , id: target.id };
            } else if (target instanceof Planet) {
                return { className: PlanetClassName, id: target.id };
            } else if (target instanceof Project) {
                return { className: ProjectClassName, id: target.id };
            } else {
                return null;
            }
        }

        function buildActionProvider(): Record<string, unknown> | undefined | null {
            const className = action.actionProvider.class;

            if ( className !== null) {
                return { className: className, id: action.actionProvider.id };
            }

            return null;
        }
    },


    loadAction: async(actionId: number): Promise<Action | null> => {
        store.dispatch('action/setLoading', { loading: true });
        const actionData = await ApiService.get(ACTION_ENDPOINT + '/' + actionId)
            .finally(() => (store.dispatch('action/setLoading', { loading: false })));
        store.dispatch('action/setLoading', { loading: false });
        let action = null;
        if (actionData.data) {
            action = (new Action()).load(actionData.data);
        }

        return action;
    }

};
export default ActionService;
