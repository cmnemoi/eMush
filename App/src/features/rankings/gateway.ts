import urlJoin from "url-join";
import { RankingDaedalus } from "./models";
import ApiService from "@/services/api.service";

const API_URL = import.meta.env.VITE_APP_API_URL;
const DAEDALUS_RANKING_ENDPOINT = urlJoin(API_URL, "daedaluses/ranking?language={language}&page={page}&itemsPerPage={itemsPerPage}&sort={sort}");

export const gateway = {
    loadDaedalusRanking: async(language: string, page: number, itemsPerPage: number, sort: string): Promise<RankingDaedalus> => {
        const response = await ApiService.get(
            DAEDALUS_RANKING_ENDPOINT
                .replace("{language}", language)
                .replace("{page}", page.toString())
                .replace("{itemsPerPage}", itemsPerPage.toString())
                .replace("{sort}", sort)
        );

        return response.data;
    }
};
