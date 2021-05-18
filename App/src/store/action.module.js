import ActionService from "@/services/action.service";

const actions = {
    async executeAction({ dispatch }, { target, action }) {
        dispatch("player/setLoading", { loading: true }, { root: true });
        dispatch("communication/clearRoomLogs", null, { root: true });
        await ActionService.executeTargetAction(target, action);
        await dispatch("player/reloadPlayer", null, { root: true });
        await dispatch("communication/loadRoomLogs", null, { root: true });
    }
};

export const action = {
    namespaced: true,
    state: {},
    getters: {},
    actions,
    mutations: {}
};
