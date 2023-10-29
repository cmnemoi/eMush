import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import store from "@/store";

// @ts-ignore
const IMPORT_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "import");

const ImportService = {
    importMyProfile: async(sid: string, token: string, serverLanguage: string): Promise<any> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const response = await ApiService.post(urlJoin(IMPORT_ENDPOINT, serverLanguage, sid, token));
        store.dispatch('gameConfig/setLoading', { loading: false });

        return response;
    }
};
export default ImportService;
