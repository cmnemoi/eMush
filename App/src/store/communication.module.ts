import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { ChannelType } from "@/enums/communication.enum";
import { Message } from "@/entities/Message";
import { ContactablePlayer } from "@/entities/ContactablePlayer";

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
    currentChannelNumberOfNewMessages: 0,
    timeLimit: 48,
    contactablePlayers: []
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
    },
    contactablePlayers(state) {
        return state.contactablePlayers;
    },
    tipsChannel(state) {
        return state.channels.find((channel: Channel) => channel.scope === ChannelType.TIPS);
    }
};

const actions: ActionTree<any, any> = {
    async changeChannel({ commit, dispatch, state }, { channel }) {
        if (state.loadingChannels) { return; }

        try {
            if (channel.scope === ChannelType.NEW_CHANNEL) {
                const newChannel = await CommunicationService.createPrivateChannel();
                commit('setCurrentChannel', newChannel);
                await dispatch('loadAlivePlayerChannels');
            } else {
                commit('setCurrentChannel', channel);
                await dispatch('loadMessages', { channel });
            }
        } catch (e) {
            console.error(e);
            return false;
        }
    },

    async loadAlivePlayerChannels({ getters, dispatch, commit }) {
        commit('setLoadingOfChannels', true);

        try {
            const channels = await CommunicationService.loadAlivePlayerChannels();
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

            await dispatch('loadMessages', { channel: getters.currentChannel });
            commit('setCurrentChannelNumberOfNewMessages', { channel: getters.currentChannel, numberOfNewMessages: getters.currentChannel.numberOfNewMessages });
            commit('setLoadingOfChannels', false);
            return true;
        } catch (e) {
            console.error(e);
            commit('setLoadingOfChannels', false);
            return false;
        }
    },

    async loadDeadPlayerChannels({ commit, dispatch }) {
        commit('setLoadingOfChannels', true);
        try {
            const channels = await CommunicationService.loadDeadPlayerChannels();
            const sortedChannels = sortChannels(channels);
            commit('setChannels', sortedChannels);
            commit('setCurrentChannel', sortedChannels.find((channel: Channel) => channel.scope === ChannelType.PUBLIC));
            await dispatch('loadMessages', { channel: state.currentChannel });
            commit('setCurrentChannelNumberOfNewMessages', { channel: state.currentChannel, numberOfNewMessages: state.currentChannel.numberOfNewMessages });
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

        commit('setTimeLimit', state.timeLimit + 24);
        commit('setLoadingForChannel', { channel, newStatus: true });
        try {
            const newMessages = await CommunicationService.loadMessages(channel, state.timeLimit);
            const messages = newMessages.concat(getters.messages).filter((message, index, self) => self.findIndex(m => m.id === message.id) === index);

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
            await dispatch('loadAlivePlayerChannels');
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

    async getContactablePlayers({ commit }, player) {
        commit("player/setLoading", true, { root: true });
        const contactablePlayers = await CommunicationService.getContactablePlayers(player);
        commit("player/setLoading", false, { root: true });
        commit('setContactablePlayers', contactablePlayers);
    },

    async invitePlayer({ dispatch }, { player, channel }) {
        await CommunicationService.invitePlayer(player, channel);
        await dispatch('closeInvitation');
        await dispatch('loadAlivePlayerChannels');
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
        await dispatch('loadAlivePlayerChannels');
    },

    async unfavoriteMessage({ dispatch }, message) {
        await CommunicationService.removeMessageFromFavorite(message);
        await dispatch('loadAlivePlayerChannels');
    },

    async readMessage({ commit }, message) {
        await message.read();
        await CommunicationService.readMessage(message);
        commit('setCurrentChannelNumberOfNewMessages', { channel: state.currentChannel, numberOfNewMessages: state.currentChannel.numberOfNewMessages - 1 });
    },

    async readRoomLog({ commit }, roomLog) {
        await roomLog.read();
        await CommunicationService.readRoomLog(roomLog);
        commit('setCurrentChannelNumberOfNewMessages', { channel: state.currentChannel, numberOfNewMessages: state.currentChannel.numberOfNewMessages - 1 });
    },

    async markAllRoomLogsAsRead({ getters, commit }) {
        if (state.currentChannel.scope !== ChannelType.ROOM_LOG) {
            throw new Error('Current channel is not a room log');
        }

        for (const roomLogObject of getters.messages) {
            for (const roomLog of roomLogObject.roomLogs) {
                roomLog.isUnread = false;
            }
        }
        await CommunicationService.markAllRoomLogsAsRead();
        commit('setCurrentChannelNumberOfNewMessages', { channel: state.currentChannel, numberOfNewMessages: 0 });
    },

    async markCurrentChannelAsRead({ getters, commit }) {
        await getters.messages.forEach((message: Message) => { message.readWithChildren(); });

        await CommunicationService.markChannelAsRead(state.currentChannel);
        commit('setCurrentChannelNumberOfNewMessages', { channel: state.currentChannel, numberOfNewMessages: 0 });
    },

    async markTipsChannelAsRead({ getters, commit }) {
        await CommunicationService.markTipsChannelAsRead(getters.tipsChannel);
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
    },

    setTimeLimit(state: any, timeLimit: number): void {
        state.timeLimit = timeLimit;
    },

    setContactablePlayers(state: any, contactablePlayers: ContactablePlayer[]): void {
        state.contactablePlayers = contactablePlayers;
    }
};

export function sortChannels(channels: Channel[]): Channel[] {
    const channelOrderValue = {
        [ChannelType.TIPS] : 0,
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
