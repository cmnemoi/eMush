import ApiService from "@/services/api.service";
import {Alert} from "@/entities/Alerts";

const DAEDALUS_ALERTS_ENDPOINT = process.env.VUE_APP_API_URL + 'daedalus';

const DaedalusService = {
    loadAlerts: async (daedalus) => {
        const alertsData = await ApiService.get(DAEDALUS_ALERTS_ENDPOINT + '/' + daedalus.id + '/alerts');

        let alerts = [];
        if (alertsData.data) {
            alertsData.data.forEach((data) => {
                alerts.push((new Alert()).load(data));
            });
        }
        return alerts;
    }
};
export default DaedalusService;
