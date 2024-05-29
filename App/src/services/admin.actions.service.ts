import ApiService from "@/services/api.service";
import { url } from "inspector";
import urlJoin from "url-join";

const API_URL = import.meta.env.VITE_APP_API_URL as string;
const ADMIN_ACTIONS_ENDPOINT = urlJoin(API_URL, "admin/actions");

const AdminActionsService = {
    createProjectsForOnGoingDaedaluses: async(): Promise<any> => {
        return await ApiService.post(ADMIN_ACTIONS_ENDPOINT + '/create-all-projects-for-on-going-daedaluses');
    },
    createEquipmentForOnGoingDaedaluses: async(equipmentName: string, quantity: integer, place: string): Promise<any> => {
        return await ApiService.post(
            urlJoin(ADMIN_ACTIONS_ENDPOINT, 'create-equipment-for-on-going-daedaluses'),
            {
                equipmentName: equipmentName,
                quantity: quantity,
                place: place
            }
        );
    },
    createPlayersAllInitStatusesForOnGoingDaedaluses: async(): Promise<any> => {
        return await ApiService.post(urlJoin(ADMIN_ACTIONS_ENDPOINT, 'create-all-players-init-statuses'));
    },
    deleteAllStatusesByName: async(statusName: string): Promise<any> => {
        return await ApiService.delete(urlJoin(ADMIN_ACTIONS_ENDPOINT, 'delete-all-statuses-by-name', statusName));
    },
    proposeNewNeronProjectsForOnGoingDaedaluses: async(): Promise<any> => {
        return await ApiService.put(ADMIN_ACTIONS_ENDPOINT + '/propose-new-neron-projects-for-on-going-daedaluses');
    },
    resetRulesAcceptanceForAllUsers: async(): Promise<any> => {
        return await ApiService.put(urlJoin(ADMIN_ACTIONS_ENDPOINT, 'reset-rules-acceptance'));
    }
};
export default AdminActionsService;
