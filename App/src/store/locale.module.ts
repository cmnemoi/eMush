import { ActionTree, GetterTree, MutationTree } from "vuex";
import { LocaleService } from "@/services/locale.service";
import { i18n } from "@/main";

interface LocaleModuleState {
    locale: string;
}

const state: LocaleModuleState = {
    locale: LocaleService.getLocale() || 'en'
};

const mutations: MutationTree<LocaleModuleState> = {
    setLocale(state, locale: string) {
        state.locale = locale;
    }
};

const actions: ActionTree<LocaleModuleState, LocaleModuleState> = {
    updateLocale({ commit }, locale: string) {
        i18n.global.locale = locale;
        LocaleService.saveLocale(locale);
        commit('setLocale', locale);
    }
};

const getters: GetterTree<LocaleModuleState, LocaleModuleState> = {
    currentLocale: state => state.locale
};

export const locale = {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};

