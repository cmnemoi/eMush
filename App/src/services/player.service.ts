import ApiService from "@/services/api.service";
import { Player } from "@/entities/Player";
import store from "@/store/index";
import { DeadPlayerInfo } from "@/entities/DeadPlayerInfo";
import urlJoin from "url-join";

// @ts-ignore
const ACTION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "player");

const PlayerService = {
    loadPlayer: async(playerId: number): Promise<Player | null> => {
        const playerData = await ApiService.get(ACTION_ENDPOINT + '/' + playerId);

        let player = null;
        if (playerData.data) {
            player = (new Player()).load(playerData.data);
        }

        return player;
    },

    loadDeadPlayerInfo: async(playerId: number): Promise<DeadPlayerInfo | null> => {
        store.dispatch('player/setLoading', { loading: true });
        const deadPlayerData = await ApiService.get(ACTION_ENDPOINT + '/' + playerId + '/end');

        let deadPlayer = null;
        if (deadPlayerData.data) {
            deadPlayer = (new DeadPlayerInfo()).load(deadPlayerData.data);
        }

        store.dispatch('player/setLoading', { loading: false });

        return deadPlayer;
    },

    sendEndGameRequest: (player: Player, message: string): Promise<void> => {
        const data = {
            message: message
        };

        return ApiService.post(ACTION_ENDPOINT + '/' + player.id + '/end', data)
            .then(() => {
                store.dispatch('auth/userInfo');
            });
    },

    selectCharacter: (daedalusId: number, character: string): Promise<void> => {
        return ApiService.post('player', { 'daedalus' : daedalusId, 'character': character })
            .then((response) => {
                const player = (new Player()).load(response.data);
                store.dispatch('player/storePlayer', { player: player });
                store.dispatch('auth/userInfo');
            })

        ;
    }
};
export default PlayerService;
