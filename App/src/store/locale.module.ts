import { ActionTree, GetterTree, MutationTree } from "vuex";
import { LocaleService } from "@/services/locale.service";
import { i18n } from "@/main";

const state = {
    locale: LocaleService.getLocale() || 'en'
};

const mutations: MutationTree<any> = {
    setLocale(state, locale: string) {
        state.locale = locale;
    }
};

const actions: ActionTree<any, any> = {
    updateLocale({ commit }, locale: string) {
        i18n.global.locale = locale;
        LocaleService.saveLocale(locale);
        commit('setLocale', locale);
    }
};

const getters: GetterTree<any, any> = {
    currentLocale: state => state.locale
};

export const locale = {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};

