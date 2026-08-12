import ApiService from "@/services/api.service";
import urlJoin from "url-join";

const GAME_COUNTERS_ENDPOINT = `${urlJoin(import.meta.env.VITE_APP_API_URL, "game-counters")}?v=2`;

export interface GameCounters {
    daedalusesInGame: number,
    mushKilled: number,
    messagesSent: number,
    expeditionsStarted: number,
}

const GameCountersService = {
    getGameCounters: async(): Promise<GameCounters | null> => {
        try {
            const response = await ApiService.get(GAME_COUNTERS_ENDPOINT);
            const counters = response.data as Partial<GameCounters>;

            return [counters.daedalusesInGame, counters.mushKilled, counters.messagesSent, counters.expeditionsStarted]
                .every((value) => typeof value === 'number') ? counters as GameCounters : null;
        } catch {
            return null;
        }
    }
};

export default GameCountersService;
