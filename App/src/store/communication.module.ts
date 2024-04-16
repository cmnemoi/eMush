import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { ChannelType } from "@/enums/communication.enum";
import { Message } from "@/entities/Message";


const state =  {
    currentChannel: new Channel(),
    invitablePlayerMenuOpen: false,
    invitablePlayers: [],
    invitationChannel: null,
    channels: [],
    loadingChannels: false,
    loadingByChannelId: {},
    messagesByChannelId: {},
    typedMessage: '',
    readMessageMutex: false,
    currentChannelNumberOfNewMessages: 0
};

const getters: GetterTree<any, any> = {
    loading(state) {
        return state.loadingByChannelId[state.currentChannel.id] || false;
    },
    messages(state) {
        return state.messagesByChannelId[state.currentChannel.id] || [];
    },
    roomChannel(state) {
        return state.channels.find((channel: Channel) => channel.scope === ChannelType.ROOM_LOG);
    },
    invitablePlayerMenuOpen(state) {
        return state.invitablePlayerMenuOpen;
    },
    invitablePlayers(state) {
        return state.invitablePlayers;
    },
    invitationChannel(state) {
        return state.invitationChannel;
    },
    channels(state) {
        return state.channels;
    },
    typedMessage(state) {
        return state.typedMessage;
    },
    readMessageMutex(state) {
        return state.readMessageMutex;
    },
    currentChannel(state) {
        return state.currentChannel;
    },
    currentChannelNumberOfNewMessages(state) {
        return state.currentChannel.numberOfNewMessages;
    },
    favoritesChannel(state) {
        return state.channels.find((channel: Channel) => channel.scope === ChannelType.FAVORITES);
    },
    publicChannel(state) {
        return state.channels.find((channel: Channel) => channel.scope === ChannelType.PUBLIC);
    }
};

const actions: ActionTree<any, any> = {
    async changeChannel({ commit, dispatch, state }, { channel }) {
        if (state.loadingChannels) { return; }

        try {
            if (channel.scope === ChannelType.NEW_CHANNEL) {
                const newChannel = await CommunicationService.createPrivateChannel();
                commit('setCurrentChannel', newChannel);
                await dispatch('loadChannels');
            } else {
                commit('setCurrentChannel', channel);
                await dispatch('loadMessages', { channel });
            }
        } catch (e) {
            console.error(e);
            return false;
        }
    },
    async loadChannels({ getters, dispatch, commit }) {
        commit('setLoadingOfChannels', true);

        try {
            const channels = await CommunicationService.loadChannels();

            const sortedChannels = sortChannels(channels);

            commit('setChannels', sortedChannels);

            // if public channel is no longer available, reset currentChannel to room log
            if (
                state.currentChannel.scope === undefined
                || sortedChannels.filter((channel: Channel) => channel.id === state.currentChannel.id).length === 0
            ) {
                if (getters.publicChannel) {
                    commit('setCurrentChannel', getters.publicChannel);
                } else {
                    commit('setCurrentChannel', getters.roomChannel);
                }
            }

            await dispatch('loadMessages', { channel: state.currentChannel });
            commit('setLoadingOfChannels', false);
            return true;
        } catch (e) {
            console.error(e);
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
            console.error(e);
            commit('setLoadingForChannel', { channel, newStatus: false });
            return false;
        }
    },

    async loadMoreMessages({ getters, commit }) {
        const channel = state.currentChannel;
        const channelMessages = getters.messages;
        const page = channelMessages.length / Channel.MESSAGE_LIMIT + 1;

        commit('setLoadingForChannel', { channel, newStatus: true });
        try {
            const newMessages = await CommunicationService.loadMessages(channel, page);
            const messages = channelMessages.concat(newMessages);

            commit('setChannelMessages', { channel, messages });
            commit('setLoadingForChannel', { channel, newStatus: false });
            return true;
        } catch (e) {
            console.error(e);
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
            console.error(e);
            commit('setLoadingForChannel', { channel, newStatus: false });
            return false;
        }
    },
    async leavePrivateChannel({ getters, commit, dispatch }, channel) {
        commit('setLoadingOfChannels', true);
        try {
            await CommunicationService.leaveChannel(channel);
            commit('removeChannel', channel);
            commit('setCurrentChannel', getters.roomChannel);
            await dispatch('loadChannels');
            commit('setLoadingOfChannels', false);
        } catch (e) {
            console.error(e);
            commit('setLoadingOfChannels', false);
        }
    },
    async getInvitablePlayersToPrivateChannel({ commit }, channel) {
        commit('invitablePlayerMenu', { isOpen: true, channel: channel });
        commit("player/setLoading", true, { root: true });
        const invitablePlayers = await CommunicationService.loadInvitablePlayers(channel);
        commit("player/setLoading", false, { root: true });
        commit('setInvitablePlayers', { invitablePlayers: invitablePlayers });
    },

    async invitePlayer({ dispatch }, { player, channel }) {
        await CommunicationService.invitePlayer(player, channel);
        await dispatch('closeInvitation');
        await dispatch('loadChannels');
    },

    async closeInvitation({ commit }) {
        commit('setInvitablePlayers', { invitablePlayers: [] });
        commit('invitablePlayerMenu', { isOpen: false, channel: null });
    },

    async clearChannels({ commit }) {
        commit('clearChannels');
    },

    clearRoomLogs({ getters, commit }) {
        commit('setChannelMessages', { channel: getters.roomChannel, messages: [] });
    },
    async loadRoomLogs({ getters, dispatch }) {
        await dispatch('loadMessages', { channel: getters.roomChannel });
    },

    updateTypedMessage({ commit }, message) {
        commit('setTypedMessage', message);
    },

    acquireReadMessageMutex({ commit }) {
        commit('setReadMessageMutex', true);
    },

    releaseReadMessageMutex({ commit }) {
        commit('setReadMessageMutex', false);
    },

    async favoriteMessage({ dispatch }, message) {
        await CommunicationService.putMessageInFavorite(message);
        await dispatch('loadChannels');
    },

    async unfavoriteMessage({ dispatch }, message) {
        await CommunicationService.removeMessageFromFavorite(message);
        await dispatch('loadChannels');
    },

    async readMessage({ commit }, message) {
        await CommunicationService.readMessage(message);
        // @FIXME: if you reload the page by clicking on eMush logo, the number of new messages is not updated...
        commit('setCurrentChannelNumberOfNewMessages', { channel: state.currentChannel, numberOfNewMessages: state.currentChannel.numberOfNewMessages - 1 });
    },

    async readRoomLog({ commit }, roomLog) {
        await CommunicationService.readRoomLog(roomLog);
        // @FIXME: if you do an action, the number of new messages is not updated...
        commit('setCurrentChannelNumberOfNewMessages', { channel: state.currentChannel, numberOfNewMessages: state.currentChannel.numberOfNewMessages - 1 });
    },

    async markCurrentChannelAsRead({ getters, commit }) {
        await CommunicationService.markChannelAsRead(state.currentChannel);
        getters.messages.forEach((message: Message) => {
            message.isUnread = false;
        });

        commit('setCurrentChannelNumberOfNewMessages', { channel: state.currentChannel, numberOfNewMessages: 0 });
    }
};

const mutations: MutationTree<any> = {
    setLoadingOfChannels(state: any, newStatus: string): void {
        state.loadingChannels = newStatus;
    },

    setLoadingForChannel(state: any, { channel, newStatus }): void {
        if (!channel) return;
        state.loadingByChannelId[channel.id] = newStatus;
    },

    setCurrentChannel(state: any, channel: Channel | null): void {
        state.currentChannel = channel;
    },

    setChannels(state: any, channels: Channel[]): void {
        state.channels = channels;
    },

    removeChannel(state: any, channel: Channel): void {
        state.channels = state.channels.filter(({ id }: {id: number}) => id !== channel.id);
        delete state.loadingByChannelId[channel.id];
        delete state.messagesByChannelId[channel.id];
    },

    invitablePlayerMenu(state: any, { isOpen, channel }): void {
        state.invitablePlayerMenuOpen = isOpen;
        state.invitationChannel = channel;
    },

    setInvitablePlayers(state: any, { invitablePlayers }): void {
        state.invitablePlayers = invitablePlayers;
    },

    setChannelMessages(state: any, { channel, messages }): void {
        if (!channel) return;
        state.messagesByChannelId[channel.id] = messages;
    },

    setTypedMessage(state: any, message: string): void {
        state.typedMessage = message;
    },

    clearChannels(): void {
        state.currentChannel = new Channel();
        state.invitationChannel = null;
        state.invitablePlayerMenuOpen = false;
        state.invitablePlayers = [];
        state.loadingByChannelId = {};
        state.messagesByChannelId = {};
        state.channels = [];
        state.readMessageMutex = false;
        state.currentChannelNumberOfNewMessages = 0;
        state.typedMessage = '';
    },

    setReadMessageMutex(state: any, mutex: boolean): void {
        state.readMessageMutex = mutex;
    },

    setCurrentChannelNumberOfNewMessages(state: any, { channel, numberOfNewMessages }): void {
        if (channel.id === state.currentChannel.id) {
            state.currentChannel.numberOfNewMessages = Math.max(0, numberOfNewMessages);
        }
    }
};

export function sortChannels(channels: Channel[]): Channel[] {
    const channelOrderValue = {
        // TODO: not implemented yet
        // [ChannelType.TIPS] : 0,
        [ChannelType.MUSH] : 1,
        [ChannelType.ROOM_LOG] : 2,
        [ChannelType.PUBLIC] : 3,
        [ChannelType.FAVORITES] : 4,
        [ChannelType.PRIVATE] : 5,
        [ChannelType.NEW_CHANNEL] : 6
    };

    return channels.sort(function (a: Channel, b: Channel) : number {
        const diff = channelOrderValue[a.scope] - channelOrderValue[b.scope];

        // sort private channels by ascending creation date
        if (diff === 0 && a.scope === ChannelType.PRIVATE) {
            return b.createdAt.getTime() - a.createdAt.getTime();
        }

        return diff;
    });
}

export const communication = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
