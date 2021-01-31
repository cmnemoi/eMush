import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";
import { ROOM_LOG } from '@/enums/communication.enum';


const state =  {
    currentChannel: new Channel(),
    channels: [],
    loadingChannels: false,
    loadingByChannelId: {},
    messagesByChannelId: {}
};

const getters = {
    loading(state) {
        return state.loadingByChannelId[state.currentChannel.id] || false;
    },
    messages(state) {
        return state.messagesByChannelId[state.currentChannel.id] || [];
    },
    roomChannel(state) {
        return state.channels.find(channel => channel.scope === ROOM_LOG);
    }
};

const actions = {
    async changeChannel({ commit }, { channel }) {
        commit('setCurrentChannel', channel);
    },
    async loadChannels({ getters, commit }) {
        commit('setLoadingOfChannels', true);

        try {
            const channels = await CommunicationService.loadChannels();
            commit('setChannels', channels);
            commit('setCurrentChannel', getters.roomChannel);
            commit('setLoadingOfChannels', false);
            return true;
        } catch (e) {
            commit('setLoadingOfChannels', false);
            return false;
        }
    },

    async loadMessages({ commit }, { channel }) {
        commit('setLoadingForChannel', { channel, newStatus: true });

        try {
            const messages = await CommunicationService.loadMessages(channel);
            commit('setChannelMessages', { channel, messages });
            commit('setLoadingForChannel', { channel, newStatus: false });
            return true;
        } catch (e) {
            commit('setLoadingForChannel', { channel, newStatus: false });
            return false;
        }
    },

    async sendMessage({ commit }, { channel, text, parent }) {
        commit('setLoadingForChannel', { channel, newStatus: true });

        try {
            const messages = await CommunicationService.sendMessage(channel, text, parent);
            commit('setChannelMessages', { channel, messages });
            commit('setLoadingForChannel', { channel, newStatus: false });
            return true;
        } catch (e) {
            commit('setLoadingForChannel', { channel, newStatus: false });
            return false;
        }
    },

    async createPrivateChannel({ state, commit }) {
        if (state.loadingChannels) { return; }
        commit('setLoadingOfChannels', true);

        try {
            const newChannel = await CommunicationService.createPrivateChannel();
            commit('addChannel', newChannel);
            commit('setLoadingOfChannels', false);

            return true;
        } catch (e) {
            commit('setLoadingOfChannels', false);
            return false;
        }
    },

    async leavePrivateChannel({ getters, commit }, channel) {
        commit('setLoadingOfChannels', true);
        try {
            await CommunicationService.leaveChannel(channel);
            commit('removeChannel', channel);
            commit('setCurrentChannel', getters.roomChannel);
            commit('setLoadingOfChannels', false);
        } catch (e) {
            commit('setLoadingOfChannels', false);
        }
    },

    clearRoomLogs({ getters, commit }) {
        commit('setChannelMessages', { channel: getters.roomChannel, messages: [] });
    },
    async loadRoomLogs({ getters, dispatch }) {
        await dispatch('loadMessages', { channel: getters.roomChannel });
    }
};

const mutations = {
    setLoadingOfChannels(state, newStatus) {
        state.loadingChannels = newStatus;
    },

    setLoadingForChannel(state, { channel, newStatus }) {
        state.loadingByChannelId[channel.id] = newStatus;
    },

    setCurrentChannel(state, channel) {
        state.currentChannel = channel;
    },

    setChannels(state, channels) {
        state.channels = channels;
    },

    addChannel(state, channel) {
        state.channels.push(channel);
    },

    removeChannel(state, channel) {
        state.channels = state.channels.filter(({ id }) => id !== channel.id);
        delete state.loadingByChannelId[channel.id];
        delete state.messagesByChannelId[channel.id];
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
