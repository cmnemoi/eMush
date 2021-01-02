import { UserService, AuthenticationError } from '../services/user.service'
import { TokenService } from '../services/storage.service'
import ApiService from "@/services/api.service";


const state =  {
    authenticating: false,
    userInfo: TokenService.getUserInfo(),
    userInfoError: '',
    userInfoErrorCode: 0,
    accessToken: TokenService.getToken(),
    authenticationErrorCode: 0,
    authenticationError: ''
};

const getters = {
    loggedIn: (state) => {
        return state.accessToken ? true : false
    },

    authenticationErrorCode: (state) => {
        return state.authenticationErrorCode
    },

    authenticationError: (state) => {
        return state.authenticationError
    },

    getUserInfo: (state) => {
        return state.userInfo;
    },

    userInfoErrorCode: (state) => {
        return state.userInfoErrorCode
    },

    userInfoError: (state) => {
        return state.userInfoError
    },

    authenticating: (state) => {
        return state.authenticating
    }
};

const actions = {
    async setToken({ commit }, {token}) {
        TokenService.saveToken(token);
        ApiService.setHeader();

        ApiService.mount401Interceptor();

        await UserService.userInfo();

        commit('loginSuccess', token)
    },

    redirect({commit}, {passphrase}) {
        commit('loginRedirect');
        UserService.redirect(passphrase);
    },

    async login({ commit }, {code}) {
        commit('loginRequest');

        try {
            const token = await UserService.login(code);
            commit('loginSuccess', token)

            return true
        } catch (e) {
            if (e instanceof AuthenticationError) {
                commit('loginError', {errorCode: e.errorCode, errorMessage: e.message})
            }

            return false
        }
    },

    refreshToken({ commit, state }) {
        // If this is the first time the refreshToken has been called, make a request
        // otherwise return the same promise to the caller
        if(!state.refreshTokenPromise) {
            let p = UserService.refreshToken()
            commit('refreshTokenPromise', p)
            // Wait for the UserService.refreshToken() to resolve. On success set the token and clear promise
            // Clear the promise on error as well.
            p.then(
                response => {
                    commit('refreshTokenPromise', null)
                    commit('loginSuccess', response)
                },
                () => {
                    commit('refreshTokenPromise', null)
                }
            )
        }
        return state.refreshTokenPromise
    },

    async userInfo({ commit, state }) {
        if(state.accessToken) {
            commit('userInfoRequest')
            try {
                let userInfo = await UserService.userInfo();
                commit('userInfoSuccess', userInfo)

                return userInfo
            } catch (e) {
                if (e instanceof AuthenticationError) {
                    commit('userInfoError', {errorCode: e.errorCode, errorMessage: e.message})
                }

                return null
            }
        }
        return null;
    },

    logout({ commit }) {
        UserService.logout()
        commit('logoutSuccess')
    }
};

const mutations = {
    loginRedirect(state) {
        state.userInfo = null;
    },
    userInfoRequest(state) {
        state.userInfo = null;
    },

    userInfoSuccess(state, userInfo) {
        state.userInfo = userInfo;
    },

    userInfoError(state, {errorCode, errorMessage}) {
        state.userInfoErrorCode = errorCode;
        state.userInfoError = errorMessage
    },

    loginRequest(state) {
        state.authenticating = true;
        state.authenticationError = '';
        state.authenticationErrorCode = 0
    },

    loginSuccess(state, accessToken) {
        state.accessToken = accessToken;
        state.authenticating = false;
    },

    loginError(state, {errorCode, errorMessage}) {
        state.authenticating = false;
        state.authenticationErrorCode = errorCode;
        state.authenticationError = errorMessage
    },

    logoutSuccess(state) {
        state.accessToken = ''
    },

    refreshTokenPromise(state, promise) {
        state.refreshTokenPromise = promise
    }
};

export const auth = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};