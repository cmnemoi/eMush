import { UserService } from '../services/user.service';
import { TokenService } from '../services/storage.service';
import ApiService from "@/services/api.service";


const state =  {
    userInfo: TokenService.getUserInfo(),
    accessToken: TokenService.getToken(),
    refreshTokenPromise: null,
    loading: false
};

const getters = {
    loggedIn: (state) => {
        return state.accessToken ? true : false;
    },

    getUserInfo: (state) => {
        return state.userInfo;
    },

    isLoading: (state) => {
        return state.loading;
    }
};

const actions = {
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

    refreshToken({ commit, state }) {
        // If this is the first time the refreshToken has been called, make a request
        // otherwise return the same promise to the caller
        if(! state.refreshTokenPromise) {
            const promise = UserService.refreshToken();
            commit('setRefreshTokenPromise', promise);
            // Wait for the UserService.refreshToken() to resolve. On success set the token and clear promise
            // Clear the promise on error as well.
            promise.then(
                response => {
                    commit('setRefreshTokenPromise', null);
                    commit('setToken', response);
                },
                () => {
                    commit('setRefreshTokenPromise', null);
                }
            );
        }
    },

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
    resetUserInfo(state) {
        state.loading = true;
        state.userInfo = null;
    },

    setUserInfo(state, userInfo) {
        state.loading = false;
        state.userInfo = userInfo;
    },

    setToken(state, accessToken) {
        state.accessToken = accessToken;
    },

    resetToken(state) {
        state.accessToken = "";
    },

    setRefreshTokenPromise(state, promise) {
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
