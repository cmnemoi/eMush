import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { Achievement, Statistic } from "./models";

const API_URL = import.meta.env.VITE_APP_API_URL as string;
const GET_USER_STATISTICS_ENDPOINT = urlJoin(API_URL, "statistics?userId={userId}&language={language}");
const GET_USER_ACHIEVEMENTS_ENDPOINT = urlJoin(API_URL, "achievements?userId={userId}&language={language}");

export type AchievementsGateway = typeof achievementsGateway;

export const achievementsGateway = {
    fetchUserStatistics: async (userId: string, language: string): Promise<Statistic[]> => {
        const response = await ApiService.get(
            GET_USER_STATISTICS_ENDPOINT
                .replace("{userId}", userId)
                .replace("{language}", language)
        );
        return response.data as Statistic[];
    },
    fetchUserAchievements: async (userId: string, language: string): Promise<Achievement[]> => {
        const response = await ApiService.get(
            GET_USER_ACHIEVEMENTS_ENDPOINT
                .replace("{userId}", userId)
                .replace("{language}", language)
        );
        return response.data as Achievement[];
    }
};
