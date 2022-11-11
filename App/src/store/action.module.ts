import ActionService from "@/services/action.service";
import { ActionTree } from "vuex";

const actions: ActionTree<any, any> = {
    async executeAction({ dispatch }, { target, action }) {
        dispatch("player/setLoading", { loading: true }, { root: true });
        dispatch("communication/clearRoomLogs", null, { root: true });
        await ActionService.executeTargetAction(target, action);
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
