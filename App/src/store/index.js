import {auth} from "./auth.module";
import { createStore } from 'vuex'

export default createStore({
    modules: {
        auth,
    },
})