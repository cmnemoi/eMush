import ActionService from "@/services/action.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import store from "@/store/index";
import { Hunter } from "@/entities/Hunter";
import { ActionEnum } from "@/enums/action.enum";

const state = {
    isHunterBeenHit: false,
    isHunterBeenKilled: false,
};

const getters: GetterTree<any, any> = {
    isHunterBeenHit: (state: any): boolean => {
        return state.isHunterBeenHit;
    },
    isHunterBeenKilled: (state: any): boolean => {
        return state.isHunterBeenKilled;
    }
};

const actions: ActionTree<any, any> = {
    async executeAction({ commit, dispatch }, { target, action, params = {} }) {
        const isLoading: boolean = store.getters["player/isLoading"];
        if (isLoading) {
            return;
        }

        dispatch("player/setLoading", { loading: true }, { root: true });
        dispatch("communication/clearRoomLogs", null, { root: true });
        
        const response = await ActionService.executeTargetAction(target, action, params);

        // Special handle to enable hunter hit animation if a hunter is hit by the relevant action
        if (target instanceof Hunter && action.key === ActionEnum.SHOOT_HUNTER) {
            commit("setIsHunterBeenHit", response.data.actionResult === "success");
            commit("setIsHunterBeenKilled", !response.data.actionDetails.hunterIsAlive);
        }

        await dispatch("communication/loadRoomLogs", null, { root: true });
        await dispatch("communication/loadChannels", null, { root: true });
        await dispatch("player/reloadPlayer", null, { root: true });
    }
};

const mutations: MutationTree<any> = {
    setIsHunterBeenHit(state: any, isHunterBeenHit: boolean): void {
        state.isHunterBeenHit = isHunterBeenHit;
    },
    setIsHunterBeenKilled(state: any, isHunterBeenKilled: boolean): void {
        state.isHunterBeenKilled = isHunterBeenKilled;
    }
};

export const action = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
