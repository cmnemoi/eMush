import ApiService from "@/services/api.service";
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
    proposeNewNeronProjectsForOnGoingDaedaluses: async(): Promise<any> => {
        return await ApiService.put(ADMIN_ACTIONS_ENDPOINT + '/propose-new-neron-projects-for-on-going-daedaluses');
    }
};
export default AdminActionsService;
