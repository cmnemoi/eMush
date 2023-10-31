import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import store from "@/store";
import { HunterConfig } from "@/entities/Config/HunterConfig";

let HUNTER_CONFIG_ENDPOINT = "";
if (process.env.VUE_APP_API_URL !== undefined) {
    HUNTER_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "hunter_configs");
}

const HunterConfigService = {
    createHunterConfig: async(hunterConfig: HunterConfig): Promise<HunterConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });

        const hunterConfigRecord: Record<string, any> = hunterConfig.jsonEncode();
        const hunterConfigData = await ApiService.post(HUNTER_CONFIG_ENDPOINT, hunterConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (hunterConfigData.data) {
            hunterConfig = (new HunterConfig()).load(hunterConfigData.data);
        }

        return hunterConfig;

    },

    loadHunterConfig: async(hunterConfigId: number): Promise<HunterConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const hunterConfigData = await ApiService.get(HUNTER_CONFIG_ENDPOINT + '/' + hunterConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));
        store.dispatch('gameConfig/setLoading', { loading: false });
        let hunterConfig = null;
        if (hunterConfigData.data) {
            hunterConfig = (new HunterConfig()).load(hunterConfigData.data);
        }
        console.log(hunterConfig);

        return hunterConfig;
    },

    updateHunterConfig: async(hunterConfig: HunterConfig): Promise<HunterConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const hunterConfigData = await ApiService.put(HUNTER_CONFIG_ENDPOINT + '/' + hunterConfig.id, hunterConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (hunterConfigData.data) {
            hunterConfig = (new HunterConfig()).load(hunterConfigData.data);
        }

        return hunterConfig;
    }
};
export default HunterConfigService;
