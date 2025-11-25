import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { Achievement, Gender, Statistic } from "./models";

const API_URL = import.meta.env.VITE_APP_API_URL as string;
const GET_USER_STATISTICS_ENDPOINT = urlJoin(API_URL, "statistics?userId={userId}&language={language}&gender={gender}");
const GET_USER_ACHIEVEMENTS_ENDPOINT = urlJoin(API_URL, "achievements?userId={userId}&language={language}&gender={gender}");

export type AchievementsGateway = typeof achievementsGateway;

export const achievementsGateway = {
    fetchUserStatistics: async (userId: string, language: string, gender: Gender): Promise<Statistic[]> => {
        const response = await ApiService.get(
            GET_USER_STATISTICS_ENDPOINT
                .replace("{userId}", userId)
                .replace("{language}", language)
                .replace("{gender}", gender)
        );
        return response.data as Statistic[];
    },
    fetchUserAchievements: async (userId: string, language: string, gender: Gender): Promise<Achievement[]> => {
        const response = await ApiService.get(
            GET_USER_ACHIEVEMENTS_ENDPOINT
                .replace("{userId}", userId)
                .replace("{language}", language)
                .replace("{gender}", gender)
        );
        return response.data as Achievement[];
    }
};
