import {auth} from "./auth.module";
import { createStore } from 'vuex'
import {player} from "@/store/player.module";

export default createStore({
    modules: {
        auth,
        player,
    },
})