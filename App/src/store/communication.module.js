import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";
import { ROOM_LOG } from '@/enums/communication.enum';


const state =  {
    loading: false,
    currentChannel: new Channel(),
    channels: [],
    messagesByChannelId: {}
};

const getters = {
    messages(state) {
        return state.messagesByChannelId[state.currentChannel.id] || [];
    }
};

const actions = {
    async changeChannel({ commit }, { channel }) {
        commit('setCurrentChannel', channel);
    },
    async loadChannels({ commit }) {
        commit('setLoading', true);

        try {
            const channels = await CommunicationService.loadChannels();
            commit('setChannels', channels);
            commit('setCurrentChannel', channels.find(({ scope }) => scope === ROOM_LOG));
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
            commit('setChannelMessages', { channel, messages });
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
            commit('setChannelMessages', { channel, messages });
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

    setChannelMessages(state, { channel, messages }) {
        state.messagesByChannelId[channel.id] = messages;
    }
};

export const communication = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
