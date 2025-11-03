import { User } from "@/entities/User";
import { TokenService } from '@/services/storage.service';
import { UserService } from '@/services/user.service';
import { ActionTree } from "vuex";

export interface AuthState {
    userInfo: User | null
    isAuthenticated: boolean
    loading: boolean,
    hasAcceptedRules: boolean
}

const state =  {
    userInfo: TokenService.getUserInfo(),
    isAuthenticated: false, // Will be determined by checking if user can make authenticated requests
    loading: false,
    hasAcceptedRules: false
};

const getters = {
    loggedIn: (state : AuthState): boolean => {
        return state.isAuthenticated && state.userInfo !== null;
    },

    getUserInfo: (state: AuthState): User | null => {
        return state.userInfo;
    },

    isLoading: (state: AuthState): boolean => {
        return state.loading;
    },

    userId: (state: AuthState): string | null => {
        return state.userInfo?.userId ?? null;
    },

    username: (state: AuthState): string | null => {
        return state.userInfo ? state.userInfo.username : null;
    },

    isAdmin: (state: AuthState): boolean => {
        return state.userInfo ? state.userInfo.isAdmin() : false;
    },

    isModerator: (state: AuthState): boolean => {
        return state.userInfo ? state.userInfo.isModerator() : false;
    },

    hasAcceptedRules: (state: AuthState): boolean => {
        return state.hasAcceptedRules;
    }
};

const actions: ActionTree<any, any> = {
    redirectToRegister({ commit }): Promise<void> {
        commit('resetUserInfo');
        return UserService.redirectToRegister();
    },

    redirectToLogin({ commit }): Promise<void> {
        commit('resetUserInfo');
        return UserService.redirectToLogin();
    },

    async login({ commit }, { code }): Promise<boolean> {
        try {
            await UserService.login(code);
            commit('setAuthenticated', true);
            await this.dispatch('auth/userInfo');

            return true;
        } catch (e) {
            console.error(e);
            commit('setAuthenticated', false);
            return false;
        }
    },

    async userInfo({ commit }): Promise<User|null> {
        commit('resetUserInfo');
        try {
            const userInfo = await UserService.userInfo();
            commit('setUserInfo', userInfo);
            commit('setAuthenticated', true);
            return userInfo;
        } catch (e) {
            console.error(e);
            commit('setAuthenticated', false);
            return null;
        } finally {
            commit('setLoading', false);
        }
    },

    async logout({ commit }) {
        await UserService.logout();
        commit('resetAuth');
    },

    async loadHasAcceptedRules({ commit }): Promise<void> {
        try {
            const hasAcceptedRules = await UserService.userInfo().then((user: User) => {
                return user.hasAcceptedRules;
            });
            commit('setHasAcceptedRules', hasAcceptedRules);
        } catch (error) {
            console.error(error);
        }
    },

    async acceptRules({ commit }): Promise<void> {
        try {
            await UserService.acceptRules();
            commit('setHasAcceptedRules', true);
        } catch (error) {
            console.error(error);
        }
    }
};

const mutations = {
    resetUserInfo(state: AuthState): void {
        state.loading = true;
        state.userInfo = null;
    },

    setUserInfo(state: AuthState, userInfo: User): void {
        state.loading = false;
        state.userInfo = userInfo;
    },

    setAuthenticated(state: AuthState, isAuthenticated: boolean): void {
        state.isAuthenticated = isAuthenticated;
    },

    resetAuth(state: AuthState): void {
        state.isAuthenticated = false;
        state.userInfo = null;
        state.hasAcceptedRules = false;
    },

    setHasAcceptedRules(state: AuthState, hasAcceptedRules: boolean): void {
        state.hasAcceptedRules = hasAcceptedRules;
    },

    setLoading(state: AuthState, loading: boolean): void {
        state.loading = loading;
    }
};

export const auth = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
