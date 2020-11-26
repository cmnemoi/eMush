import ApiService from "@/services/api.service";
import {Player} from "@/entities/Player";

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
    }
}
export default PlayerService