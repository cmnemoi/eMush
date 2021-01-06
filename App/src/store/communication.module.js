import { AuthenticationError } from "@/services/user.service";
import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";
import { ROOM_LOG, TIPS } from "@/enums/communication.enum";


const state =  {
    loading: false,
    currentChannel: null,
    channels: [],
    messages: []
};

const getters = {
    getCurrentChannel: (state) => {
        if (state.currentChannel === null) {
            state.currentChannel = (new Channel()).decode(localStorage.getItem('currentChannel'));
        }

        return state.currentChannel;

    },
    getChannels: (state) => {
        return state.channels;
    },
    getMessages: (state) => {
        return state.messages;
    },
    loading: (state) => {
        return state.loading;
    }
};

const actions = {
    async changeChannel({ commit }, { channel }) {
        localStorage.setItem('currentChannel', channel.jsonEncode());
        commit('changeChannel', channel);
    },
    async loadChannels({ commit }) {
        commit('loadRequest');

        try {
            const channels = await CommunicationService.loadChannels();
            channels.reverse();

            const roomLogChannel = new Channel();
            roomLogChannel.scope = ROOM_LOG;
            channels.push(roomLogChannel);

            const tipsChannel = new Channel();
            tipsChannel.scope = TIPS;
            channels.push(tipsChannel);

            channels.reverse();
            commit('loadSuccess', channels);

            return true;
        } catch (e) {
            if (e instanceof AuthenticationError) {
                commit('loadError', { errorCode: e.errorCode, errorMessage: e.message });
            }

            return false;
        }
    },

    async loadMessages({ commit }, { channel }) {
        commit('loadMessagesRequest');

        try {
            const messages = await CommunicationService.loadMessages(channel);
            commit('loadMessagesSuccess', messages);

            return true;
        } catch (e) {
            if (e instanceof AuthenticationError) {
                commit('loadMessagesError', { errorCode: e.errorCode, errorMessage: e.message });
            }

            return false;
        }
    },

    async sendMessage({ commit }, { channel, text, parent }) {
        commit('loadMessagesRequest');

        try {
            const messages = await CommunicationService.sendMessage(channel, text, parent);
            commit('loadMessagesSuccess', messages);

            return true;
        } catch (e) {
            if (e instanceof AuthenticationError) {
                commit('loadMessagesError', { errorCode: e.errorCode, errorMessage: e.message });
            }

            return false;
        }
    },

    async createPrivateChannel({ commit }) {
        commit('createRequest');

        try {
            const channels = await CommunicationService.createPrivateChannel();
            channels.reverse();

            const roomLogChannel = new Channel();
            roomLogChannel.scope = ROOM_LOG;
            channels.push(roomLogChannel);

            const tipsChannel = new Channel();
            tipsChannel.scope = TIPS;
            channels.push(tipsChannel);

            channels.reverse();
            commit('createSuccess', channels);

            return true;
        } catch (e) {
            if (e instanceof AuthenticationError) {
                commit('createError', { errorCode: e.errorCode, errorMessage: e.message });
            }

            return false;
        }
    }
};

const mutations = {
    loadRequest(state) {
        state.loading = true;
    },

    loadMessagesRequest(state) {
        state.loading = true;
    },

    changeChannel(state, channel) {
        state.currentChannel = channel;
    },

    createSuccess(state, channels) {
        state.channels = channels;
        state.loading = false;
    },

    loadSuccess(state, channels) {
        state.channels = channels;
        state.loading = false;
    },

    loadMessagesSuccess(state, messages) {
        state.currentChannel.messages = messages;
        state.loading = false;
    },

    loadError(state, { errorCode, errorMessage }) {
        state.playerErrorCode = errorCode;
        state.playerError = errorMessage;
        state.loading = false;
    },

    createError(state, { errorCode, errorMessage }) {
        state.playerErrorCode = errorCode;
        state.playerError = errorMessage;
        state.loading = false;
    },

    loadMessagesError(state, { errorCode, errorMessage }) {
        state.playerErrorCode = errorCode;
        state.playerError = errorMessage;
        state.loading = false;
    }
};

export const communication = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
