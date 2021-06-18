import ApiService from "@/services/api.service";
import { Player } from "@/entities/Player";
import store from "@/store/index";
import { DeadPlayerInfo } from "@/entities/DeadPlayerInfo";
import urlJoin from "url-join";

const ACTION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "player");

const PlayerService = {
    /**
     *
     * @param playerId
     */
    loadPlayer: async(playerId) => {
        const playerData = await ApiService.get(urlJoin(ACTION_ENDPOINT, playerId.toString()));

        let player = null;
        if (playerData.data) {
            player = (new Player()).load(playerData.data);
        }

        return player;
    },

    loadDeadPlayerInfo: async(playerId) => {
        store.dispatch('player/setLoading', { loading: true });
        const deadPlayerData = await ApiService.get(urlJoin(ACTION_ENDPOINT, playerId, "end"));

        let deadPlayer = null;
        if (deadPlayerData.data) {
            deadPlayer = (new DeadPlayerInfo()).load(deadPlayerData.data);
        }

        store.dispatch('player/setLoading', { loading: false });

        return deadPlayer;
    },

    sendEndGameRequest: (player, message, likes) => {
        let data = {
            message: message,
            likes: likes
        };

        return ApiService.post(urlJoin(ACTION_ENDPOINT, player.id, "end"), data)
            .then(() => {
                store.dispatch('auth/userInfo');
            });
    },

    selectCharacter: (daedalusId, character) => {
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
