import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";


const state =  {
    loading: false,
    currentChannel: null,
    channels: [],
    messages: []
};

const getters = {
    currentChannel: (state) => {
        if (state.currentChannel === null) {
            state.currentChannel = (new Channel()).decode(localStorage.getItem('currentChannel'));
        }

        return state.currentChannel;
    }
};

const actions = {
    async changeChannel({ commit }, { channel }) {
        localStorage.setItem('currentChannel', channel.jsonEncode());
        commit('setCurrentChannel', channel);
    },
    async loadChannels({ commit }) {
        commit('setLoading', true);

        try {
            const channels = await CommunicationService.loadChannels();
            commit('setChannels', channels);
            commit('setLoading', false);
            return true;
        } catch (e) {
            commit('setLoading', false);
            return false;
        }
    },

    async loadMessages({ commit }, { channel }) {
        commit('setLoading', true);

        try {
            const messages = await CommunicationService.loadMessages(channel);
            commit('setCurrentChannelMessages', messages);
            commit('setLoading', false);
            return true;
        } catch (e) {
            commit('setLoading', false);
            return false;
        }
    },

    async sendMessage({ commit }, { channel, text, parent }) {
        commit('setLoading', true);

        try {
            const messages = await CommunicationService.sendMessage(channel, text, parent);
            commit('setCurrentChannelMessages', messages);
            commit('setLoading', false);
            return true;
        } catch (e) {
            commit('setLoading', false);
            return false;
        }
    },

    async createPrivateChannel({ commit }) {
        commit('setLoading', true);

        try {
            const channels = await CommunicationService.createPrivateChannel();
            commit('setChannels', channels);
            commit('setLoading', false);

            return true;
        } catch (e) {
            commit('setLoading', false);
            return false;
        }
    }
};

const mutations = {
    setLoading(state, newStatus) {
        state.loading = newStatus;
    },

    setCurrentChannel(state, channel) {
        state.currentChannel = channel;
    },

    setChannels(state, channels) {
        state.channels = channels;
    },

    setCurrentChannelMessages(state, messages) {
        state.currentChannel.messages = messages;
    }
};

export const communication = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
