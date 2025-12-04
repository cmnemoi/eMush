import urlJoin from "url-join";
import { UserSearchResult } from "./models";
import ApiService from "@/services/api.service";

const API_URL = import.meta.env.VITE_APP_API_URL;
const USER_SEARCH_ENDPOINT = urlJoin(API_URL, "users/search?username={username}&limit={limit}");

export const gateway = {
    searchUsers: async (username: string, limit: number): Promise<UserSearchResult[]> => {
        const response = await ApiService.get(
            USER_SEARCH_ENDPOINT
                .replace("{username}", encodeURIComponent(username))
                .replace("{limit}", limit.toString())
        );

        return response.data;
    }
};
