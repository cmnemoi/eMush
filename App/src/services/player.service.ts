import ApiService from "@/services/api.service";
import { Player } from "@/entities/Player";
import store from "@/store/index";
import { ClosedPlayer } from "@/entities/ClosedPlayer";
import { DeadPlayerInfo } from "@/entities/DeadPlayerInfo";
import urlJoin from "url-join";

// @ts-ignore
const PLAYER_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "player");
// @ts-ignore
const CLOSED_PLAYER_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "closed_players");

const PlayerService = {
    addLikeToPlayer: async (player: Player): Promise<void> => {
        const params = {
            'player': player.id
        };
        await ApiService.post(PLAYER_ENDPOINT + '/' + player.id + '/like', params);
        await store.dispatch('auth/userInfo');
    },
    loadPlayer: async(playerId: number): Promise<Player | null> => {
        const playerData = await ApiService.get(PLAYER_ENDPOINT + '/' + playerId);

        let player = null;
        if (playerData.data) {
            player = (new Player()).load(playerData.data);
        }

        return player;
    },

    loadDeadPlayerInfo: async(playerId: number): Promise<DeadPlayerInfo | null> => {
        await store.dispatch('player/setLoading', { loading: true });
        const deadPlayerData = await ApiService.get(PLAYER_ENDPOINT + '/' + playerId);

        let deadPlayer = null;
        if (deadPlayerData.data) {
            deadPlayer = (new DeadPlayerInfo()).load(deadPlayerData.data);
        }

        await store.dispatch('player/setLoading', { loading: false });

        return deadPlayer;
    },

    sendEndGameRequest: async (player: Player, message: string, likedPlayers: number[]): Promise<void> => {
        const data = {
            message: message,
            likedPlayers: likedPlayers
        };

        return await ApiService.post(PLAYER_ENDPOINT + '/' + player.id + '/end', data)
            .then(() => {
                store.dispatch('player/clearPlayer');
                store.dispatch('auth/userInfo');
            });
    },

    selectCharacter: async (userId: number, daedalusId: number, character: string): Promise<void> => {
        return await ApiService.post('player', { 'user' : userId, 'daedalus' : daedalusId, 'character': character })
            .then((response) => {
                const player = (new Player()).load(response.data);
                store.dispatch('player/storePlayer', { player: player });
                store.dispatch('auth/userInfo');
            })

        ;
    },

    loadClosedPlayer: async(playerId: number): Promise<ClosedPlayer | null> => {
        const closedPlayerData = await ApiService.get(CLOSED_PLAYER_ENDPOINT + '/' + playerId);

        let closedPlayer = null;
        if (closedPlayerData.data) {
            closedPlayer = (new ClosedPlayer()).load(closedPlayerData.data);
        }

        return closedPlayer;
    },

    triggerCycleChange: async (player: Player): Promise<void> => {
        await store.dispatch('player/setLoading', { loading: true });
        return ApiService.get(PLAYER_ENDPOINT + '/' + player.id + '/cycle-change')
            .then(async () => {
                await Promise.all([
                    store.dispatch("communication/clearRoomLogs", null, { root: true }),
                    store.dispatch('player/reloadPlayer', { playerId: player.id }),
                    store.dispatch("communication/loadRoomLogs", null, { root: true }),
                    store.getters['player/player'].isAlive() ? store.dispatch("communication/loadAlivePlayerChannels", null, { root: true }) : store.dispatch("communication/loadDeadPlayerChannels", null, { root: true })
                ]);
            });
    },

    triggerExplorationCycleChange: async (player: Player): Promise<void> => {
        await store.dispatch('player/setLoading', { loading: true });
        return ApiService.get(PLAYER_ENDPOINT + '/' + player.id + '/exploration-cycle-change')
            .then(async () => {
                await Promise.all([
                    store.dispatch("communication/clearRoomLogs", null, { root: true }),
                    store.dispatch('player/reloadPlayer', { playerId: player.id }),
                    store.dispatch("communication/loadRoomLogs", null, { root: true }),
                    store.getters['player/player'].isAlive() ? store.dispatch("communication/loadAlivePlayerChannels", null, { root: true }) : store.dispatch("communication/loadDeadPlayerChannels", null, { root: true })
                ]);
            });
    }
};
export default PlayerService;
