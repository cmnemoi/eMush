import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { GameConfig } from "@/entities/Config/GameConfig";
import { ModifierCondition } from "@/entities/Config/ModifierCondition";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import store from "@/store";

// @ts-ignore
const GAME_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "game_configs");
// @ts-ignore
const MODIFIER_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "modifier_configs");
// @ts-ignore
const MODIFIER_CONDITION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "modifier_conditions");

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
        console.log('err');

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
        console.log('err');

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
    }
};
export default GameConfigService;
