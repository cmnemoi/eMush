import { ActionTree, GetterTree, MutationTree } from "vuex";

const state = {
    code: '',
    sid: ''
};

const getters: GetterTree<any, any> = {
    code: (state: any): string => {
        return state.code;
    },
    sid: (state: any): string => {
        return state.sid;
    }
};

const actions : ActionTree<any, any> = {
    async updateCode({ commit }, code: string): Promise<void> {
        commit('setCode', code);
    }
};

const mutations: MutationTree<any> = {
    setCode(state: any, code: string): void {
        state.code = code;
    },
    setSid(state: any, sid: string): void {
        state.sid = sid;
    }
};

export const twinoidImport = {
    namespaced: true,
    state,
    actions,
    getters,
    mutations
};
