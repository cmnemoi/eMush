import ApiService from "@/services/api.service";
import {Player} from "@/entities/Player";
import store from "@/store/index"
const ACTION_ENDPOINT = process.env.VUE_APP_API_URL+'player'

const PlayerService = {
    /**
     *
     * @param playerId
     */
    loadPlayer: async(playerId) => {
        const playerData = await ApiService.get(ACTION_ENDPOINT + '/' + playerId)

        let player = null;
        if (playerData.data) {
            player = (new Player()).load(playerData.data);
        }

        return player;
    },

    selectCharacter: (daedalusId, character) => {
        return ApiService.post('player', {'daedalus' : daedalusId, 'character': character})
            .then((response) => {
                const player = (new Player()).load(response.data);
                store.dispatch('player/storePlayer', {player: player});
                store.dispatch('auth/userInfo');
            })

        ;
    }
}
export default PlayerService