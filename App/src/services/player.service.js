import ApiService from "@/services/api.service";
import { Player } from "@/entities/Player";
import store from "@/store/index";
import {DeadPlayerInfo} from "@/entities/DeadPlayerInfo";
const ACTION_ENDPOINT = process.env.VUE_APP_API_URL+'player';

const PlayerService = {
    /**
     *
     * @param playerId
     */
    loadPlayer: async(playerId) => {
        const playerData = await ApiService.get(ACTION_ENDPOINT + '/' + playerId);

        let player = null;
        if (playerData.data) {
            player = (new Player()).load(playerData.data);
        }

        return player;
    },

    loadDeadPlayerInfo: async(playerId) => {
        const deadPlayerData = await ApiService.get(ACTION_ENDPOINT + '/' + playerId + '/end');

        let deadPlayer = null;
        if (deadPlayerData.data) {
            deadPlayer = (new DeadPlayerInfo()).load(deadPlayerData.data);
        }

        return deadPlayer;
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
