import ApiService from "@/services/api.service";
import urlJoin from "url-join";

const API_URL = import.meta.env.VITE_APP_API_URL as string;
const STATS_ENDPOINT = urlJoin(API_URL, "stats");

const AdminActionsService = {
    getPlayerSkillCount: async(skillName : string): Promise<string> => {
        return (await ApiService.post(STATS_ENDPOINT + '/skills', { 'skillName' : skillName })).data;
    },

    getAllPlayerSkillCount: async(): Promise<string> => {
        return (await ApiService.post(STATS_ENDPOINT + '/skills/all')).data;
    },

    getCharacterSkillsCount: async(character : string): Promise<string> => {
        return (await ApiService.post(STATS_ENDPOINT + '/skills/characters', { 'characterName' : character })).data;
    },
    getExploFightData: async(daedalusId : number): Promise<string> => {
        return (await ApiService.post(STATS_ENDPOINT + '/explorations/fights', { 'daedalusId' : daedalusId })).data;
    },

    getPlayerSkillList: async(): Promise<Array<string>> => {
        return (await ApiService.post(STATS_ENDPOINT + '/skills/list')).data;
    },
    getCharacterList: async(): Promise<Array<string>> => {
        return (await ApiService.post(STATS_ENDPOINT + '/characters/list')).data;
    }
};
export default AdminActionsService;
