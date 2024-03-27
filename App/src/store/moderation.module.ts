import { ModerationSanction } from "@/entities/ModerationSanction";
import ModerationSanctionService from "@/services/moderation_sanction.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";

const state = {
    userWarnings: [] as ModerationSanction[]
};

const getters: GetterTree<any, any> = {
    userWarnings: (state: any): ModerationSanction[] => {
        return state.userWarnings;
    }
};

const actions: ActionTree<any, any> = {
    async loadUserWarnings({ commit }, userId: string): Promise<boolean> {
        try {
            const userWarnings = await ModerationSanctionService.getUserActiveWarnings(userId)
                .then((response: ModerationSanction[]) => {
                    return response;
                });
            commit('setUserWarnings', userWarnings);
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }
};

const mutations: MutationTree<any> = {
    setUserWarnings(state: any, warnings: ModerationSanction[]): void {
        state.userWarnings = warnings;
    }
};

export const moderation = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
