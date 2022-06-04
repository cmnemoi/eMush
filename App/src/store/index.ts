import { action } from "@/store/action.module";
import { auth } from "@/store/auth.module";
import { createStore } from 'vuex';
import { error } from "@/store/error.module";
import { player } from "@/store/player.module";
import { room } from "@/store/room.module";
import { communication } from "@/store/communication.module";
import { daedalus } from "@/store/daedalus.module";

export default createStore({
    modules: {
        action,
        auth,
        error,
        player,
        room,
        communication,
        daedalus
    }
})
;
