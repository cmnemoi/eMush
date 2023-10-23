import ActionService from "@/services/action.service";
import { ActionTree } from "vuex";
import store from "@/store/index";

const actions: ActionTree<any, any> = {
    async executeAction({ dispatch }, { target, action, params = {} }) {
        const isLoading: boolean = store.getters["player/isLoading"];
        if (isLoading) {
            return;
        }
        dispatch("player/setLoading", { loading: true }, { root: true });
        dispatch("communication/clearRoomLogs", null, { root: true });
        await ActionService.executeTargetAction(target, action, params);
        await dispatch("player/reloadPlayer", null, { root: true });
        await dispatch("communication/loadRoomLogs", null, { root: true });
        await dispatch("communication/loadChannels", null, { root: true });
    }
};

export const action = {
    namespaced: true,
    state: {},
    getters: {},
    actions,
    mutations: {}
};
