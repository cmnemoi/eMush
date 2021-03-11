import { action } from "@/store/action.module";
import { auth } from "@/store/auth.module";
import { createStore } from 'vuex';
import { error } from "@/store/error.module";
import { player } from "@/store/player.module";
import { communication } from "@/store/communication.module";

export default createStore({
    modules: {
        action,
        auth,
        error,
        player,
        communication
    }
})
;
