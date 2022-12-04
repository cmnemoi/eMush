import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { GameConfig } from "@/entities/Config/GameConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { ModifierCondition } from "@/entities/Config/ModifierCondition";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import store from "@/store";
import { ActionCost } from "@/entities/Config/ActionCost";
import { ActionConfig } from "@/entities/Config/ActionConfig";

// @ts-ignore
const GAME_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "game_configs");
// @ts-ignore
const MODIFIER_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "modifier_configs");
// @ts-ignore
const MODIFIER_CONDITION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "modifier_conditions");
// @ts-ignore
const CONFIG_STATUS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "status_configs");
// @ts-ignore
const CONFIG_ACTION_COST_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "action_costs");
// @ts-ignore
const CONFIG_ACTION_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "actions");
// @ts-ignore
const CONFIG_DAEDALUS_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "daedalus_configs");

const GameConfigService = {
    loadGameConfig: async(gameConfigId: number): Promise<GameConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const gameConfigData = await ApiService.get(GAME_CONFIG_ENDPOINT + '/' + gameConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));
        store.dispatch('gameConfig/setLoading', { loading: false });
        let gameConfig = null;
        if (gameConfigData.data) {
            gameConfig = (new GameConfig()).load(gameConfigData.data);
        }

        return gameConfig;
    },

    updateGameConfig: async(gameConfig: GameConfig): Promise<GameConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const gameConfigData = await ApiService.put(GAME_CONFIG_ENDPOINT + '/' + gameConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', gameConfig)
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (gameConfigData.data) {
            gameConfig = (new GameConfig()).load(gameConfigData.data);
        }

        return gameConfig;
    },

    loadModifierConfig: async(modifierConfigId: number): Promise<ModifierConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierConfigData = await ApiService.get(MODIFIER_CONFIG_ENDPOINT + '/' + modifierConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let modifierConfig = null;
        if (modifierConfigData.data) {
            modifierConfig = (new ModifierConfig()).load(modifierConfigData.data);
        }

        return modifierConfig;
    },

    updateModifierConfig: async(modifierConfig: ModifierConfig): Promise<ModifierConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierConfigData = await ApiService.put(MODIFIER_CONFIG_ENDPOINT + '/' + modifierConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', modifierConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (modifierConfigData.data) {
            modifierConfig = (new ModifierConfig()).load(modifierConfigData.data);
        }

        return modifierConfig;
    },

    loadModifierCondition: async(modifierConditionId: number): Promise<ModifierCondition | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierConditionData = await ApiService.get(MODIFIER_CONDITION_ENDPOINT + '/' + modifierConditionId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let modifierCondition = null;
        if (modifierConditionData.data) {
            modifierCondition = (new ModifierCondition()).load(modifierConditionData.data);
        }

        return modifierCondition;
    },

    updateModifierCondition: async(modifierCondition: ModifierCondition): Promise<ModifierCondition | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierConditionData = await ApiService.put(MODIFIER_CONDITION_ENDPOINT + '/' + modifierCondition.id + '?XDEBUG_SESSION_START=PHPSTORM', modifierCondition)
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (modifierConditionData.data) {
            modifierCondition = (new ModifierCondition()).load(modifierConditionData.data);
        }

        return modifierCondition;
    },

    loadStatusConfig: async(statusConfigId: number): Promise<StatusConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const statusConfigData = await ApiService.get(CONFIG_STATUS_ENDPOINT + '/' + statusConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let statusConfig = null;
        if (statusConfigData.data) {
            statusConfig = (new StatusConfig()).load(statusConfigData.data);
        }

        return statusConfig;
    },

    updateStatusConfig: async(statusConfig: StatusConfig): Promise<StatusConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const statusConfigData = await ApiService.put(CONFIG_STATUS_ENDPOINT + '/' + statusConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', statusConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (statusConfigData.data) {
            statusConfig = (new StatusConfig()).load(statusConfigData.data);
        }

        return statusConfig;
    },

    loadActionConfig: async(actionConfigId: number): Promise<ActionConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const actionConfigData = await ApiService.get(CONFIG_ACTION_CONFIG_ENDPOINT + '/' + actionConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let actionConfig = null;
        if (actionConfigData.data) {
            actionConfig = (new ActionConfig()).load(actionConfigData.data);
        }

        return actionConfig;
    },

    updateActionConfig: async(actionConfig: ActionConfig): Promise<ActionConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const actionConfigData = await ApiService.put(CONFIG_ACTION_CONFIG_ENDPOINT + '/' + actionConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', actionConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (actionConfigData.data) {
            actionConfig = (new ActionConfig()).load(actionConfigData.data);
        }

        return actionConfig;
    },

    loadActionCost: async(actionCostId: number): Promise<ActionCost | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const actionCostData = await ApiService.get(CONFIG_ACTION_COST_ENDPOINT + '/' + actionCostId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let actionCost = null;
        if (actionCostData.data) {
            actionCost = (new ActionCost()).load(actionCostData.data);
        }

        return actionCost;
    },

    updateActionCost: async(actionCost: ActionCost): Promise<ActionCost | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const actionCostData = await ApiService.put(CONFIG_ACTION_COST_ENDPOINT + '/' + actionCost.id + '?XDEBUG_SESSION_START=PHPSTORM', actionCost.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (actionCostData.data) {
            actionCost = (new ActionCost()).load(actionCostData.data);
        }

        return actionCost;
    },

    loadDaedalusConfig: async(daedalusConfigId: number): Promise<DaedalusConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const daedalusConfigData = await ApiService.get(CONFIG_DAEDALUS_CONFIG_ENDPOINT + '/' + daedalusConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let daedalusConfig = null;
        if (daedalusConfigData.data) {
            daedalusConfig = (new DaedalusConfig()).load(daedalusConfigData.data);
        }

        return daedalusConfig;
    },

    updateDaedalusConfig: async(daedalusConfig: DaedalusConfig): Promise<DaedalusConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const daedalusConfigData = await ApiService.put(CONFIG_DAEDALUS_CONFIG_ENDPOINT + '/' + daedalusConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', daedalusConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (daedalusConfigData.data) {
            daedalusConfig = (new DaedalusConfig()).load(daedalusConfigData.data);
        }

        return daedalusConfig;
    }
};
export default GameConfigService;
