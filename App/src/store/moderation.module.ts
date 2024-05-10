import { ModerationSanction } from "@/entities/ModerationSanction";
import ModerationSanctionService from "@/services/moderation_sanction.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";

const state = {
    userSanctions: [] as ModerationSanction[]
};

const getters: GetterTree<any, any> = {
    userSanctions: (state: any): ModerationSanction[] => {
        return state.userSanctions;
    }
};

const actions: ActionTree<any, any> = {
    async loadUserSanctions({ commit }, userId: integer): Promise<boolean> {
        try {
            const userSanctions = await ModerationSanctionService.getUserActiveSanctions(userId)
                .then((response: ModerationSanction[]) => {
                    return response;
                });
            commit('setUserSanctions', userSanctions);
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }
};

const mutations: MutationTree<any> = {
    setUserSanctions(state: any, sanctions: ModerationSanction[]): void {
        state.userSanctions = sanctions;
    }
};

export const moderation = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
