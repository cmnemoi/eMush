import { auth } from "./auth.module";
import { createStore } from 'vuex';
import { error } from "@/store/error.module";
import { player } from "@/store/player.module";
import { communication } from "@/store/communication.module";
import { traduction } from "@/store/traduction.module";

export default createStore({
    modules: {
        auth,
        error,
        player,
        communication,
        traduction
    }
})
;
