import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Hunter } from "@/entities/Hunter";
import { SpaceBattle } from "@/entities/SpaceBattle";

const state =  {
    loading: false,
    spaceBattle: null,
    selectedTarget: null
};

const getters: GetterTree<any, any> = {
    selectedTarget: (state) => {
        return state.selectedTarget;
    }
};

const actions: ActionTree<any, any> = {
    loadSpaceBattle({ commit }, { spaceBattle }) {
        commit('setSpaceBattle', spaceBattle);
    },
    async reloadPlayer({ state, dispatch }) {
        return dispatch("loadPlayer", { playerId: state.player.id });
    },
    setLoading({ commit }, { loading }) {
        commit('setLoading', loading);
    },
    selectTarget({ commit }, { target }) {
        commit('setSelectedTarget', target);
    },
};

const mutations : MutationTree<any> = {
    setLoading(state, newValue) {
        state.loading = newValue;
    },
    setSpaceBattle(state, room: SpaceBattle | null) {
        state.room = room;
    },
    setSelectedTarget(state, target: Hunter | null) {
        state.selectedTarget = target;
    },
};

export const room = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
