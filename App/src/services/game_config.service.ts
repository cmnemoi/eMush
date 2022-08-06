import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { GameConfig } from "@/entities/Config/GameConfig";
import store from "@/store";

// @ts-ignore
const ACTION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "game_configs");

const GameConfigService = {
    loadDefaultGameConfig: async(gameConfigId: number): Promise<GameConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const gameConfigData = await ApiService.get(ACTION_ENDPOINT + '/' + gameConfigId + '?XDEBUG_SESSION_START=PHPSTORM');
        store.dispatch('gameConfig/setLoading', { loading: false });

        let gameConfig = null;
        if (gameConfigData.data) {
            gameConfig = (new GameConfig()).load(gameConfigData.data);
        }

        return gameConfig;
    },

    updateDefaultGameConfig: async(gameConfig: GameConfig): Promise<GameConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const gameConfigData = await ApiService.put(ACTION_ENDPOINT + '/' + gameConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', gameConfig)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (gameConfigData.data) {
            gameConfig = (new GameConfig()).load(gameConfigData.data);
        }

        return gameConfig;
    },
};
export default GameConfigService;
