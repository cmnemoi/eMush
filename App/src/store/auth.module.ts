import { UserService } from '@/services/user.service';
import { TokenService } from '@/services/storage.service';
import ApiService from "@/services/api.service";
import { User } from "@/entities/User";
import { ActionTree } from "vuex";


export interface AuthState {
    userInfo: User | null
    accessToken: null | string
    refreshTokenPromise: null | string
    loading: boolean
}

const state =  {
    userInfo: TokenService.getUserInfo(),
    accessToken: TokenService.getToken(),
    refreshTokenPromise: null,
    loading: false
};

const getters = {
    loggedIn: (state : AuthState) => {
        return state.accessToken ? true : false;
    },

    getUserInfo: (state: AuthState) => {
        return state.userInfo;
    },

    isLoading: (state: AuthState) => {
        return state.loading;
    }
};

const actions: ActionTree<any, any> = {
    async setToken({ commit }, { token }) {
        TokenService.saveToken(token);
        ApiService.setHeader();

        await UserService.userInfo();

        commit('setToken', token);
    },

    redirect({ commit }, { passphrase }) {
        commit('resetUserInfo');
        UserService.redirect(passphrase);
    },

    async login({ commit }, { code }) {
        try {
            const token = await UserService.login(code);
            commit('setToken', token);
            await this.dispatch('auth/userInfo');

            return true;
        } catch (e) {
            return false;
        }
    },

    // refreshToken({ commit, state }) {
    //     // If this is the first time the refreshToken has been called, make a request
    //     // otherwise return the same promise to the caller
    //     if(! state.refreshTokenPromise) {
    //         const promise = UserService.refreshToken();
    //         commit('setRefreshTokenPromise', promise);
    //         // Wait for the UserService.refreshToken() to resolve. On success set the token and clear promise
    //         // Clear the promise on error as well.
    //         promise.then(
    //             response => {
    //                 commit('setRefreshTokenPromise', null);
    //                 commit('setToken', response);
    //             },
    //             () => {
    //                 commit('setRefreshTokenPromise', null);
    //             }
    //         );
    //     }
    // },

    async userInfo({ commit, state }) {
        if (state.accessToken) {
            commit('resetUserInfo');
            try {
                let userInfo = await UserService.userInfo();
                commit('setUserInfo', userInfo);
                return userInfo;
            } catch (e) {
                return null;
            }
        }
        return null;
    },

    logout({ commit }) {
        UserService.logout();
        commit('resetToken');
    }
};

const mutations = {
    resetUserInfo(state: AuthState) {
        state.loading = true;
        state.userInfo = null;
    },

    setUserInfo(state: AuthState, userInfo: User) {
        state.loading = false;
        state.userInfo = userInfo;
    },

    setToken(state: AuthState, accessToken: string) {
        state.accessToken = accessToken;
    },

    resetToken(state: AuthState) {
        state.accessToken = null;
    },

    setRefreshTokenPromise(state: AuthState, promise:string) {
        state.refreshTokenPromise = promise;
    }
};

export const auth = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
