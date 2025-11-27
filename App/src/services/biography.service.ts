import ApiService from "@/services/api.service";
import urlJoin from "url-join";

const API_URL = import.meta.env.VITE_APP_API_URL;
const charactersEndpoint = urlJoin(API_URL, "biography");

export const BiographyService = {
    async loadCharacterBiography(characterName: string, language: string): Promise<string> {
        const base = urlJoin(charactersEndpoint, characterName);
        const url = `${base}?language=${language}`;
        const response = await ApiService.get(url);
        return response.data;
    }
};
