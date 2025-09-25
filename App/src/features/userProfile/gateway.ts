import urlJoin from "url-join";
import { ShipHistory } from "./models";
import ApiService from "@/services/api.service";

const API_URL = import.meta.env.VITE_APP_API_URL;
const SHIPS_HISTORY_ENDPOINT = urlJoin(API_URL, "players/ships-history?userId={userId}&page={page}&itemsPerPage={itemsPerPage}&language={language}");

export const gateway = {
    loadShipsHistory: async(userId: string, page: number, itemsPerPage: number, language: string): Promise<ShipHistory[]> => {
        const response = await ApiService.get(
            SHIPS_HISTORY_ENDPOINT
                .replace("{userId}", userId)
                .replace("{page}", page.toString())
                .replace("{itemsPerPage}", itemsPerPage.toString())
                .replace("{language}", language)
        );

        return response.data;
    }
};
